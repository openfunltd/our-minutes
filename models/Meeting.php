<?php

class MeetingRow extends MiniEngine_Table_Row
{
    public function getURL($type)
    {
        foreach (['host', 'screen', 'join'] as $t) {
            if ($t != $type) {
                continue;
            }
            if (!$this->d("{$t}_secret")) {
                $this->change(["{$t}_secret" => MiniEngineHelper::uniqid(10)]);
            }
            return sprintf("https://%s/meeting/%s/%s/%s",
                $_SERVER['HTTP_HOST'],
                $t,
                $this->uid,
                $this->d("{$t}_secret"),
            );
        }
    }

    public function change($data)
    {
        $origin_data = json_decode($this->data);
        foreach ($data as $k => $v) {
            $origin_data->{$k} = $v;
        }
        $this->data = json_encode($origin_data);
        $this->save();
    }

    public function d($key)
    {
        return json_decode($this->data)->{$key} ?? null;
    }
}

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
