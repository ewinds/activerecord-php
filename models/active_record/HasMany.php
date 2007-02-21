<?php
require_once 'Association.php';
class HasMany extends Association {
  function __construct(&$source, $dest, $options=null) {
    parent::__construct($source, $dest, $options);
    $this->foreign_key = Inflector::foreign_key($this->source_class);
  }

  function push($args, &$source) {
    foreach ($args as $object) {
      if (($source->is_new_record() || $object->is_new_record())
                                    && $this->options['through'])
        throw new ActiveRecordException("HasManyThroughCantAssociateNewRecords", ActiveRecordException::HasManyThroughCantAssociateNewRecords);
      if (!$object instanceof $this->dest_class) {
        throw new ActiveRecordException("Expected class: {$this->dest_class}; Received: ".get_class($object), ActiveRecordException::UnexpectedClass);
      }
      if ($source->is_new_record()) {
        /* we want to save $object after $source gets saved */
        $object->set_modified(true);
      }
      elseif (!$this->options['through']) {
        /* since source exists, we always want to save $object */
        $object->{$this->foreign_key} = $source->{$source->get_primary_key()};
        $this->get($source);
        $object->save();
      }
      elseif ($this->options['through']) {
        /* $object and $source are guaranteed to exist in the DB */
        $this->get($source);
        $skip = false;
        foreach ($this->value as $val)
          if ($val == $object) $skip = true;
        if (!$skip) {
          $through_class = Inflector::classify($this->options['through']);
          $fk_1 = Inflector::foreign_key($this->dest_class);
          $fk_2 = Inflector::foreign_key($this->source_class);
          $k1   = $object->{$object->get_primary_key()};
          $k2   = $source->{$source->get_primary_key()};
          $through = new $through_class( array($fk_1 => $k1, $fk_2 => $k2) );
          $through->save();
        }
      }
      $this->get($source);
      array_push($this->value, $object);
    }
  }

  function get(&$source, $force=false) {
    if (!is_array($this->value) || $force) {
      if ($source->is_new_record()) {
        $this->value = array();
        return $this->value; 
      }
      try {
        if (!$this->options['through']) {
          $collection = call_user_func_array(array($this->dest_class, 'find'),
            array('all',
              array('conditions' => "{$this->foreign_key} = ".$source->{$source->get_primary_key()})));
        }
        else {
          // TODO: $this->options['through'] is not necessarily the table name
          $collection = call_user_func_array(array($this->dest_class, 'find'),
            array('all',
              array('include' => $this->options['through'],
                'conditions' => "{$this->options['through']}.{$this->foreign_key} = ".$source->{$source->get_primary_key()})));
        }
      } catch (ActiveRecordExeception $e) {
      }
      $collection = is_null($collection) ? array() : $collection;
      $this->value = $collection;
    }
    return $this->value;
  }

  function join() {
    $dest_table = Inflector::tableize($this->dest_class);
    $source_table = Inflector::tableize($this->source_class);
    $source_inst = new $this->source_class;
    $dest_inst = new $this->dest_class;
    $columns = $dest_inst->get_columns();
    if (!$this->options['through']) {
      $join = "LEFT OUTER JOIN $dest_table ON "
            . "$dest_table.{$this->foreign_key} = $source_table.".$source_inst->get_primary_key();
    }
    else {
      $join = "LEFT OUTER JOIN {$this->options['through']} ON "
            . "{$this->options['through']}.{$this->foreign_key} = $source_table.".$source_inst->get_primary_key() ." "
            . "LEFT OUTER JOIN $dest_table ON "
            . "$dest_table.".$dest_inst->get_primary_key() ." = {$this->options['through']}." . Inflector::foreign_key($this->dest_class);
    }
    return array( array($dest_table => $columns), $join);
  }

  function populate_from_find($attributes) {
    $class = $this->dest_class;
    $item = new $class($attributes);
    $item->new_record = false;
    if (!is_array($this->value))
      $this->value = array();
    array_push($this->value, $item);
  }

  function needs_saving() {
    if (!is_array($this->value))
      return false;
    else {
      foreach ($this->value as $val)
        if ($val->is_modified() || $val->is_new_record())
          return true;
    }
    return false;
  }

  function save_as_needed($source) {
    foreach ($this->value as $object) {
      if ($object->is_modified() || $object->is_new_record()) {
        if (!$this->options['through'])
          $object->{$this->foreign_key} = $source->{$source->get_primary_key()};
        $object->save();
      }
    }
  }

}
?>
