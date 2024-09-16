<?php

class MeetingController extends MiniEngine_Controller
{
    public function init()
    {
        if ($user_id = MiniEngine::getSession('user_id')) {
            $this->user = $this->view->user = User::find($user_id);
        }
    }

    public function createAction()
    {
        if (!$this->user) {
            return $this->alert('need login', '/');
        }
        $this->init_csrf();
        if ($_POST) {
            if ($_POST['csrf_token'] != $this->view->csrf_token) {
                return $this->alert('csrf error', '/');
            }
            $meeting = Meeting::insert([
                'uid' => MiniEngineHelper::uniqid(10),
                'owner_id' => $this->user->id,
                'created_at' => time(),
                'data' => json_encode([
                    'name' => $_POST['name'],
                    'intro' => $_POST['intro'],
                    'time' => $_POST['time'],
                    'agenda' => $_POST['agenda'],
                ]),
            ]);
            return $this->alert('create success', '/meeting/show/' . $meeting->uid);
        }
    }

    public function showAction($meet_id)
    {
        if (!$this->user) {
            return $this->alert('need login', '/');
        }
        if (!$meeting = Meeting::search(['uid' => $meet_id])->first()) {
            return $this->notfound('meeting not found');
        }
        if ($meeting->owner_id != $this->user->id) {
            return $this->notfound('meeting not found');
        }
        $this->view->meeting = $meeting;

        if ($_POST) {
            if ($_POST['csrf_token'] != $this->view->csrf_token) {
                return $this->alert('csrf error', '/');
            }
            $meeting->data = json_encode([
                'name' => $_POST['name'],
                'intro' => $_POST['intro'],
                'time' => $_POST['time'],
                'agenda' => $_POST['agenda'],
            ]);
            $meeting->save();
            return $this->alert('update success', '/meeting/show/' . $meeting->uid);
        }
    }
}
