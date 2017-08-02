<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Mailer\Email;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use TwitterOAuth;
use OAuthConsumer;

class TwittersController extends AppController {

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Custom');
        $this->loadModel('Users');
    }

    public function beforeFilter(Event $event) {
        require_once(ROOT . DS . 'vendor' . DS . 'oauth' . DS . 'twitteroauth.php');
        $this->Auth->allow(['index', 'tweetcall', 'clearsessions', 'connect', 'twitterlogin', 'twitterSignup']);
    }

    public function index() {
        session_start();
        $session = $this->request->session();
        /* If the oauth_token is old redirect to the connect page. */
        if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
            return $this->redirect(['controller' => 'twitters', 'action' => 'clearsessions']);
        }

        /* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

        /* Request access tokens from twitter */
        $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

        /* Save the access tokens. Normally these would be saved in a database for future use. */
        $_SESSION['access_token'] = $access_token;

        /* Remove no longer needed request tokens */
        unset($_SESSION['oauth_token']);
        unset($_SESSION['oauth_token_secret']);

        /* If HTTP response is 200 continue otherwise send to connect page to retry */
        if (200 == $connection->http_code) {
            /* The user has been verified and the access tokens can be saved for future use */
            $_SESSION['status'] = 'verified';
            //$userInfo = $connection->get('account/verify_credentials');
            //$twitterUser = $field_twitter_url;
            //$userInfo = $connection->get('statuses/home_timeline', array('screen_name' => $twitterUser));
            $content = $connection->get('account/verify_credentials');
            $this->request->session()->write('twitter_data', $content);

            if (!empty($content)) {
                $firstName = $content->name;
                $username = $content->screen_name;
                $getUserDetails = $this->Users->find('all')->where(['username' => $username]);
                $getUserDetail = $this->Users->find('all')->where(['username' => $username])->first();
                if ($getUserDetails->count() != 0) {////if usertname already exist or second time login through twitter////
                    session_destroy();

                    $getLoginConfirmation = $getUserDetail->toArray();
                    $get_login = $this->Auth->setUser($getLoginConfirmation);
                    $userLoginId = $this->Auth->user('id');

                    if ($userLoginId) {
                        $user = $this->Users->newEntity();
                        $user->login_type = 3;
                        $user->last_login_ip = $this->Custom->get_ip_address();
                        $user->last_login_date = date('Y-m-d H:i:s');
                        $user->id = $userLoginId;
                        $this->Users->save($user);
                        $this->Flash->success(__('You have successfully login to free4lancer by twitter.'));
                        return $this->redirect(HTTP_ROOT . 'dashboard');
                        if ($getUserDetail->type == 0) {
                            return $this->redirect(HTTP_ROOT);
                        }
                        if ($getUserDetail->type == 3) {
                            return $this->redirect(HTTP_ROOT);
//                            return $this->redirect(['controller' => 'Publishers', 'action' => 'publisherDashboard']);
                        } else {
                            return $this->redirect(HTTP_ROOT);
//                            return $this->redirect(['controller' => 'Users', 'action' => 'home']);
                        }
                    } else {
                        $this->Flash->error(__('Login failed and you can register here to visit free4lancer.'));
                        return $this->redirect(HTTP_ROOT);
                    }
                } else { ////**Important Section***if username doesnot exist and first time login through twitter***Important Section***///////    
                    return $this->redirect('/twitter-signup');
                    exit;
                }
            } else {
                $this->Flash->error(__('Login failed and you can register here also'));
                return $this->redirect(HTTP_ROOT);
            }
        }
    }

    public function twitterSignup() {
        $this->layout = '';
        $twitter_data = $this->request->session()->read('twitter_data');
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $emailExist = $this->Users->find()->where(['Users.email' => $data['email']])->count();
            $usernameExist = $this->Users->find()->where(['Users.username' => $data['username']])->count();
            if ($emailExist > 0 || $usernameExist > 0) {
                $this->Flash->error(__('Username or email already exist!!'));
                $this->redirect($this->referer());
            } else {
                $user = $this->Users->patchEntity($user, $data);
                $user->firstname = $twitter_data->name;
                $user->sign_up_type = 3;
                $user->uniq_id = $this->Custom->generateUniqNumber();
                $user->created = date('Y-m-d H:i:s');
                $user->last_login_date = date('Y-m-d H:i:s');
                $user->account_status = 1;
                $user->register_ip = $this->Custom->get_ip_address();
                $user->last_login_ip = $this->Custom->get_ip_address();
                if ($this->Users->save($user)) {
                    $picUrl = $twitter_data->profile_image_url;
                    $dir_to_save = WWW_ROOT . USER_PROFILE_IMAGE;
                    $profileImage = "image" . rand() . '.jpg';
                    $fileName = $dir_to_save . $profileImage;
                    file_put_contents($fileName, file_get_contents($picUrl));
                    $this->Users->query()->update()->set(['profile_image' => $profileImage])->where(['id' => $user->id])->execute();
                    $getLoginConfirmation = $user->toArray();
                    $this->Auth->setUser($getLoginConfirmation);
                    $this->Flash->success(__('Now you can change your profile details'));
                    return $this->redirect(HTTP_ROOT . 'dashboard');
                } else {
                    $this->Flash->error(__('Login failed and you can register here also'));
                    return $this->redirect(HTTP_ROOT);
                }
            }
        }

        $this->set(compact('twitter_data'));
    }

    public function twitterSignupXXXX() {
        $this->layout = '';
        $twitter_data = $this->request->session()->read('twitter_data');
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $user = $this->Users->patchEntity($user, $data);
            $user->firstname = $twitter_data->name;
            $user->sign_up_type = 3;
            $user->uniq_id = $this->Custom->generateUniqNumber();
            $user->created = date('Y-m-d H:i:s');
            $user->last_login_date = date('Y-m-d H:i:s');
            $user->account_status = 1;
            $user->register_ip = $this->Custom->get_ip_address();
            $user->last_login_ip = $this->Custom->get_ip_address();
            if ($this->Users->save($user)) {
                $picUrl = $twitter_data->profile_image_url;
                $dir_to_save = WWW_ROOT . USER_PROFILE_IMAGE;
                $profileImage = "image" . rand() . '.jpg';
                $fileName = $dir_to_save . $profileImage;
                file_put_contents($fileName, file_get_contents($picUrl));
                $this->Users->query()->update()->set(['profile_image' => $profileImage])->where(['id' => $user->id])->execute();
                $getLoginConfirmation = $user->toArray();
                $this->Auth->setUser($getLoginConfirmation);
                $this->Flash->success(__('Now you can change your profile details'));
                return $this->redirect(HTTP_ROOT . 'dashboard');
            } else {
                $this->Flash->error(__('Login failed and you can register here also'));
                return $this->redirect(HTTP_ROOT);
            }
        }
        $this->set(compact('twitter_data'));
    }

    public function tweetcall() {
        $this->layout = 'innerdefault';
        /* If the oauth_token is old redirect to the connect page. */
        if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
            $_SESSION['oauth_status'] = 'oldtoken';
            $this->redirect("twitters/clearsessions");
        }

        /* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

        /* Request access tokens from twitter */
        $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

        /* Save the access tokens. Normally these would be saved in a database for future use. */
        $_SESSION['access_token'] = $access_token;

        /* Remove no longer needed request tokens */
        unset($_SESSION['oauth_token']);
        unset($_SESSION['oauth_token_secret']);

        /* If HTTP response is 200 continue otherwise send to connect page to retry */
        if (200 == $connection->http_code) {
            /* The user has been verified and the access tokens can be saved for future use */
            $_SESSION['status'] = 'verified';
            $this->redirect("/twitters");
        } else {
            /* Save HTTP status for error dialog on connnect page. */
            $this->redirect("/twitters/clearsessions");
        }
    }

    public function clearsessions() {
        session_start();
        session_destroy();
        $this->redirect("/twitters/connect");
    }

    public function connect() {
//        echo 'Jai Jagannath Swami';exit;
        $this->layout = 'innerdefault';
        App::import('Vendor', 'twitter/twitteroauth-master/config');
        if (CONSUMER_KEY === '' || CONSUMER_SECRET === '' || CONSUMER_KEY === 'CONSUMER_KEY_HERE' || CONSUMER_SECRET === 'CONSUMER_SECRET_HERE') {
            echo 'You need a consumer key and secret to test the sample code. Get one from <a href="http://192.168.1.200/2016/free4lancer/apps">http://192.168.1.200/2016/free4lancer/apps</a>';
            exit;
        }

        /* Build an image link to start the redirect process. */
        echo '<a href="' . HTTP_ROOT . '/twitters/redirect11"><img src="' . HTTP_ROOT . 'img/darker.png" alt="Sign in with Twitter"/></a>';
        exit;
        /* Include HTML to display on the page. */
//        include('html.inc');
    }

    public function twitterlogin() {

        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

        $request_token = $connection->getRequestToken(OAUTH_CALLBACK);
        //  pj($connection);
        // pj($request_token); 
        // $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
        // echo "</br>";
        //   $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
        //   exit;
        $token = $request_token['oauth_token'];
        $session = $this->request->session();
        $session->write('oauth_token', $request_token['oauth_token']); //Write
        $session->write('oauth_token_secret', $request_token['oauth_token_secret']); //Write
        // echo $session->read('oauth_token');
        // echo $session->read('oauth_token_secret');
        // exit; //To read the session value   o/p:$100,00,00
        //  $this->Session->write('oauth_token', $request_token['oauth_token']);
        //$this->Session->write('oauth_token_secret', $request_token['oauth_token_secret']);
        //   echo $green = $this->Session->read('oauth_token');
        // exit;
        switch ($connection->http_code) {
            case 200:
                $url = $connection->getAuthorizeURL($token);
                // echo $url;exit;
                $this->redirect($url);

                break;
            default:
                echo 'Could not connect to Twitter. Refresh the page or try again later.';
        }
    }

}

?>