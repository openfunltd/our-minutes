<?php

class IndexController extends MiniEngine_Controller
{
    public function init()
    {
        if ($user_id = MiniEngine::getSession('user_id')) {
            $this->view->user = User::find($user_id);
        }
    }

    public function indexAction()
    {
        $this->view->app_name = getenv('APP_NAME');
    }

    public function robotsAction()
    {
        header('Content-Type: text/plain');
        echo "#\n";
        return $this->noview();
    }
}
