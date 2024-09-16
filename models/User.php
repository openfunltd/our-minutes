<?php

class User extends MiniEngine_Table
{
    public function init()
    {
        $this->_columns['id'] = ['type' => 'serial'];
        $this->_columns['email'] = ['type' => 'varchar', 'length' => 255];
        $this->_columns['created_at'] = ['type' => 'int'];
        $this->_columns['data'] = ['type' => 'jsonb'];
    }
}
