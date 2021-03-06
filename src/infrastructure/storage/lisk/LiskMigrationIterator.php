<?php

/*
 * Copyright 2012 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Iterate over every object of a given type, without holding all of them in
 * memory. This is useful for performing database migrations.
 *
 *   $things = new LiskMigrationIterator(new LiskThing());
 *   foreach ($things as $thing) {
 *     // do something
 *   }
 *
 * NOTE: This only works on objects with a normal `id` column.
 *
 * @task storage
 */
final class LiskMigrationIterator implements Iterator {

  private $object;
  private $cursor;
  private $data;

  public function __construct(LiskDAO $object) {
    $this->object = $object;
  }

  public function rewind() {
    $this->cursor = 0;
    $this->data = array();
    $this->next();
  }

  public function valid() {
    if (!$this->data) {
      $this->next();
    }
    return (bool)$this->data;
  }

  public function current() {
    return end($this->data);
  }

  public function key() {
    return $this->current()->getID();
  }

  public function next() {
    if ($this->data) {
      return array_pop($this->data);
    }
    $this->data = $this->object->loadAllWhere(
      'id > %d ORDER BY id ASC LIMIT %d',
      $this->cursor,
      100);
    if ($this->data) {
      $this->cursor = last($this->data)->getID();
      $this->data = array_reverse($this->data);
    }
  }

}
