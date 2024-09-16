<?php

class Meeting extends MiniEngine_Table
{
    public function init()
    {
        $this->_columns['id'] = ['type' => 'serial'];
        $this->_columns['uid'] = ['type' => 'varchar', 'length' => 12];
        $this->_columns['owner_id'] = ['type' => 'int'];
        $this->_columns['created_at'] = ['type' => 'int'];
        $this->_columns['data'] = ['type' => 'jsonb'];

        $this->_indexes['uid'] = ['columns' => ['uid'], 'unique' => true];
        $this->_indexes['owner_id'] = ['columns' => ['owner_id']];
    }
}
