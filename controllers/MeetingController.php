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
            $meeting->change([
                'name' => $_POST['name'],
                'intro' => $_POST['intro'],
                'time' => $_POST['time'],
                'agenda' => $_POST['agenda'],
            ]);
            return $this->alert('update success', '/meeting/show/' . $meeting->uid);
        }
    }

    public function screenAction($meet_id, $secret)
    {
        if (!$meeting = Meeting::search(['uid' => $meet_id])->first()) {
            return $this->notfound('meeting not found');
        }
        if ($meeting->d('screen_secret') != $secret) {
            return $this->notfound('secret error');
        }
        return $this->notfound('TODO');
    }

    public function hostAction($meet_id, $secret)
    {
        if (!$meeting = Meeting::search(['uid' => $meet_id])->first()) {
            return $this->notfound('meeting not found');
        }
        if ($meeting->d('host_secret') != $secret) {
            return $this->notfound('secret error');
        }
        return $this->notfound('TODO');
    }

    public function joinAction($meet_id, $secret)
    {
        if (!$meeting = Meeting::search(['uid' => $meet_id])->first()) {
            return $this->notfound('meeting not found');
        }
        if ($meeting->d('join_secret') != $secret) {
            return $this->notfound('secret error');
        }
        $this->view->meeting = $meeting;
        $this->view->upload_url = '/meeting/uploadaudio/' . $meet_id . '/' . $secret;
    }

    public function uploadaudioAction($meet_id, $secret)
    {
        if (!$meeting = Meeting::search(['uid' => $meet_id])->first()) {
            return $this->notfound('meeting not found');
        }
        if ($meeting->d('join_secret') != $secret) {
            return $this->notfound('secret error');
        }
        if (!$_FILES) {
            return $this->json(['error' => 'no file']);
        }

        $file = $_FILES['audio'];
        if (!file_exists('/tmp/uploads')) {
            mkdir('/tmp/uploads', 0777, true);
        }
        $filename = sprintf("%s-%s-%s", $meet_id, $_POST['user_id'], $_POST['start']);
        $path = '/tmp/uploads/' . $filename . '.mp3';
        move_uploaded_file($file['tmp_name'], $path);
        return $this->json(['path' => $path]);
    }
}
