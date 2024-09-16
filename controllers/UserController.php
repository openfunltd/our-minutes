<?php

class UserController extends MiniEngine_Controller
{
    public function googleloginAction()
    {
        $return_to = 'https://' . $_SERVER['HTTP_HOST'] . '/user/googledone';
        $url = 'https://accounts.google.com/o/oauth2/auth?'
            . '&state='
            . '&scope=email'
            . '&redirect_uri=' . urlencode($return_to)
            . '&response_type=code'
            . '&client_id=' . getenv('GOOGLE_CLIENT_ID')
            . '&access_type=offline';
        return $this->redirect($url);
    }

    public function googledoneAction()
    {
        $code = $_GET['code'];
        $return_to = 'https://' . $_SERVER['HTTP_HOST'] . '/user/googledone';
        $url = 'https://www.googleapis.com/oauth2/v3/token';
        $data = array(
            'code' => $code,
            'client_id' => getenv('GOOGLE_CLIENT_ID'),
            'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => $return_to,
            'grant_type' => 'authorization_code'
        );
        try {
            $response = MiniEngine::http($url, 'POST', http_build_query($data));
        } catch (Exception $e) {
            return $this->alert('login failed', '/');
        }
        $response = json_decode($response);
        $access_token = $response->access_token;
        $url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $access_token;
        $response = MiniEngine::http($url);
        $response = json_decode($response);
        $email = $response->email;

        if (!$user = User::find_by_email($email)) {
            $user = User::insert([
                'email' => $email,
            ]);
        }
        MiniEngine::setSession('user_id', $user->id);
        return $this->redirect('/');
    }

    public function logoutAction()
    {
        MiniEngine::deleteSession('user_id');
        return $this->redirect('/');
    }
}
