<?php

namespace App\Controller\Admin;

use App\Controller\AppController;

class AppadminsController extends AppController {

    public function initialize() {
        parent::initialize();

        $this->loadComponent('Flash');
        $this->loadComponent('Paginator');
        $this->loadComponent('Custom');

        $this->loadModel('MailTemplates');
        $this->loadModel('Makes');
        $this->loadModel('Models');
        $this->loadModel('Fuels');
        $this->loadModel('BodyTypes');
        $this->loadModel('Transmissions');
        $this->loadModel('Towns');
        $this->loadModel('Banks');
        $this->loadModel('Users');
        $this->loadModel('AdminDetails');
        $this->loadModel('AdminSettings');
        $this->loadModel('CarStatus');

        $this->layout = 'admin';
        if ($this->Auth->user('type') != 1 && $this->Auth->user('type') != 2) {
            $this->redirect(HTTP_ROOT);
        }
    }

    public function auditReport() {
        $this->paginate = ['limit' => ADMIN_PAGE_LIMIT, 'order' => ['id' => 'DESC'], 'conditions' => []];
        $makes = $this->paginate($this->Makes);
        $this->set(compact('makes'));
    }

    public function requestReport() {
        $this->paginate = ['limit' => ADMIN_PAGE_LIMIT, 'order' => ['id' => 'DESC'], 'conditions' => []];
        $makes = $this->paginate($this->Makes);
        $this->set(compact('makes'));
    }

    public function clickReport() {
        $this->paginate = ['limit' => ADMIN_PAGE_LIMIT, 'order' => ['id' => 'DESC'], 'conditions' => []];
        $makes = $this->paginate($this->Makes);
        $this->set(compact('makes'));
    }

    public function userListing() {
        $this->paginate = ['limit' => ADMIN_PAGE_LIMIT, 'order' => ['id' => 'DESC'], 'conditions' => []];
        $makes = $this->paginate($this->Makes);
        $this->set(compact('makes'));
    }

//Dealer section starts here by prakash
    public function manageDealer() {
        $dealerListings = $this->Users->find()->where(['type' => 3])->contain(['DealerDetails']);
        $this->set(compact('dealerListings'));
    }

    public function verifyDealer($id = NULL) {
        if ($this->Users->query()->update()->set(['status' => 1, 'is_verified' => 1])->where(['id' => $id])->execute()) {
            $this->_sendEmailForDealerVerified($id);
            $this->Flash->success(__("This dealer acount has been verified successfully."));
            $this->redirect(HTTP_ROOT . 'admin/manage-dealer');
        } else {
            $this->Flash->error(__("We don't found any dealer of this accout"));
            $this->redirect(HTTP_ROOT . 'admin/manage-dealer');
        }
    }

    public function deleteDealer($id = NULL) {
        $this->loadModel('DealerDetails');
        $this->DealerDetails->deleteAll(['user_id' => $id]);
        $this->loadModel('DealerModels');
        $query = $this->DealerModels->find()->where(['DealerModels.user_id' => $id]);
        if ($query->count()) {
            $this->DealerModels->deleteAll(['user_id' => $id]);
        }
        $this->loadModel('DealerBodyTypes');
        $query1 = $this->DealerBodyTypes->find()->where(['DealerBodyTypes.user_id' => $id]);
        if ($query1->count()) {
            $this->DealerBodyTypes->deleteAll(['user_id' => $id]);
        }
        $data = $this->Users->get($id);
        $dataDelete = $this->Users->delete($data);
        $this->Flash->success(__(' This dealer has been deleted.'));
        return $this->redirect(HTTP_ROOT . 'admin/manage-dealer');
    }

    public function _sendEmailForDealerVerified($userId) {
        $user = $this->Users->find()->where(['Users.id' => $userId])->first();
        $emailTemplate = $this->MailTemplates->find()->where(['MailTemplates.name' => 'DEALER_VERIFIED_EMAIL'])->first();
        $adminSetting = $this->AdminSettings->find()->where(['id' => '1'])->first();
        $to = $user->email;
        $from = $adminSetting->from_email;
        $subject = $emailTemplate->subject;
        $link = "<a target='_blank' href='" . HTTP_ROOT . "login' style='background:none repeat scroll 0 0 #C20E09;border-radius:4px;color:#ffffff;display:block;font-size:14px;font-weight:bold;margin:15px 1px;padding:5px 10px;text-align:center;width:270px;text-decoration:none'>" . __('Click here to login your account') . "</a>";
        $linkText = "<a target='_blank' href='" . HTTP_ROOT . "login'>" . HTTP_ROOT . "login</a>";
        $message = $this->Custom->formatEmail($emailTemplate->content, ['LINK' => $link, 'LINK_TEXT' => $linkText, 'SUPPORT_EMAIL' => $adminSetting->support_email]);
        $this->Custom->sendEmail($to, $from, $subject, $message);
        return TRUE;
    }

//Dealer section ends here by prakash
    public function adminListing($action = NULL, $id = NULL) {
        if ($this->request->is('post')) {
            $data = $this->request->data;
            if ($action == 'add') {
                $query = $this->Users->find()->where(['email' => $data['email']]);
                if ($query->count()) {
                    $this->Flash->error(__("This email address already exists"));
                } else {
                    $userEntity = $this->Users->newEntity();
                    $userEntity->email = $data['email'];
                    $userEntity->password = $data['password'];
                    $userEntity->type = $data['type'];
                    $userEntity->id = $id;
                    if ($this->Users->save($userEntity)) {
                        $adminDetails = $this->AdminDetails->newEntity();
                        $adminDetails->user_id = $userEntity->id;
                        $adminDetails->firstname = $data['firstname'];
                        $adminDetails->lastname = $data['lastname'];
                        if ($this->AdminDetails->save($adminDetails)) {
                            $this->_sendEmailForCreateAdmin($adminDetails->user_id, $data['password']);
                            $this->Flash->success(__("Admin has been created successfully."));
                        } else {
                            $this->Flash->error(__("Some error occured, try later."));
                        }
                        $this->redirect(HTTP_ROOT . 'admin/admin-listing');
                    } else {
                        $this->Flash->error(__("Some error occured, try later."));
                        $this->redirect(HTTP_ROOT . 'admin/admin-listing');
                    }
                }
            } else if ($action == 'edit' && $id) {
                $adminDetails = $this->AdminDetails->newEntity();
                $adminDetails->id = $data['admin_detail_id'];
                $adminDetails->user_id = $id;
                $adminDetails->firstname = $data['firstname'];
                $adminDetails->lastname = $data['lastname'];
                if ($this->AdminDetails->save($adminDetails)) {
                    $this->_updateAdminProfileEmail($adminDetails->user_id);
                    $this->Flash->success(__("Admin has been updated successfully."));
                } else {
                    $this->Flash->error(__("Some error occured, try later."));
                }
                $this->redirect(HTTP_ROOT . 'admin/admin-listing');
            } else if ($action == 'reset-password') {
                if ($data['password'] != $data['confrim_password']) {
                    $this->Flash->error(__("Password and confirm password doesnot matches."));
                    $this->redirect(HTTP_ROOT . 'admin/admin-listing');
                } else {
                    $this->Users->query()->update()->set(['password' => password_hash($data['password'], PASSWORD_DEFAULT)])->where(['id' => $data['id']])->execute();
                    $this->_sendEmailForResetPasswordAdmin($data['id'], $data['password']);
                    $this->Flash->success(__("Password updated successfully."));
                    $this->redirect(HTTP_ROOT . 'admin/admin-listing');
                }
            }
        }
        if (!empty($id)) {
            $editValue = $this->Users->find()->where(['Users.id' => $id])->contain(['AdminDetails'])->first();
        }
        $adminListings = $this->Users->find()->where(['type' => 2])->contain(['AdminDetails']);
        $this->set(compact('adminListings', 'editValue'));
    }

    public function _updateAdminProfileEmail($userId) {
        $user = $this->Users->find()->where(['Users.id' => $userId])->contain(['AdminDetails'])->first();
        $emailTemplate = $this->MailTemplates->find()->where(['MailTemplates.name' => 'ADMIN_UPDATE_EMAIL'])->first();
        $adminSetting = $this->AdminSettings->find()->where(['id' => '1'])->first();
        $to = $user->email;
        $from = $adminSetting->from_email;
        $subject = $emailTemplate->subject;
        $message = $this->Custom->formatEmail($emailTemplate->content, ['FIRSTNAME' => $user->admin_detail->firstname, 'LASTNAME' => $user->admin_detail->lastname, 'EMAIL' => $user->email, 'SUPPORT_EMAIL' => $adminSetting->support_email]);
        $this->Custom->sendEmail($to, $from, $subject, $message);
        return TRUE;
    }

    public function _sendEmailForCreateAdmin($userId, $password) {
        $user = $this->Users->find()->where(['Users.id' => $userId])->contain(['AdminDetails'])->first();
        $emailTemplate = $this->MailTemplates->find()->where(['MailTemplates.name' => 'ADMIN_CREATE_EMAIL'])->first();
        $adminSetting = $this->AdminSettings->find()->where(['id' => '1'])->first();
        $to = $user->email;
        $from = $adminSetting->from_email;
        $subject = $emailTemplate->subject;
        $username = $user->admin_detail->firstname . ' ' . $user->admin_detail->lastname;
        $link = "<a target='_blank' href='" . HTTP_ROOT . "admin/login' style='background:none repeat scroll 0 0 #C20E09;border-radius:4px;color:#ffffff;display:block;font-size:14px;font-weight:bold;margin:15px 1px;padding:5px 10px;text-align:center;width:270px;text-decoration:none'>" . __('Click here to login your account') . "</a>";
        $linkText = "<a target='_blank' href='" . HTTP_ROOT . "admin/login'>" . HTTP_ROOT . "admin/login</a>";
        $message = $this->Custom->formatEmail($emailTemplate->content, ['USERNAME' => $username, 'EMAIL' => $user->email, 'PASSWORD' => $password, 'LINK' => $link, 'LINK_TEXT' => $linkText, 'SUPPORT_EMAIL' => $adminSetting->support_email]);
        $this->Custom->sendEmail($to, $from, $subject, $message);
        return TRUE;
    }

    public function _sendEmailForResetPasswordAdmin($userId, $password) {
        $user = $this->Users->find()->where(['Users.id' => $userId])->contain(['AdminDetails'])->first();
        $emailTemplate = $this->MailTemplates->find()->where(['MailTemplates.name' => 'RESET_PASSWORD_EMAIL'])->first();
        $adminSetting = $this->AdminSettings->find()->where(['id' => '1'])->first();
        $to = $user->email;
        $from = $adminSetting->from_email;
        $subject = $emailTemplate->subject;
        $username = $user->admin_detail->firstname . ' ' . $user->admin_detail->lastname;
        $message = $this->Custom->formatEmail($emailTemplate->content, ['USERNAME' => $username, 'EMAIL' => $user->email, 'PASSWORD' => $password, 'SUPPORT_EMAIL' => $adminSetting->support_email]);
        $this->Custom->sendEmail($to, $from, $subject, $message);
        return TRUE;
    }

    public function advertisements() {
        $this->paginate = ['limit' => ADMIN_PAGE_LIMIT, 'order' => ['id' => 'DESC'], 'conditions' => []];
        $makes = $this->paginate($this->Makes);
        $this->set(compact('makes'));
    }

    public function checkDuplicateName() {
        $data = $this->request->data;
        $name = $data['name'];
        $modeName = $data['params']['modelName'];
        if (!empty($data['params']['id'])) {
            $check_duplicate_name = $this->$modeName->find()->where(['name' => $name, 'id !=' => $data['params']['id']])->count();
        }
        if (empty($data['params']['id'])) {
            $check_duplicate_name = $this->$modeName->find()->where(['name' => $name])->count();
        }
        if (empty($check_duplicate_name)) {
            echo json_encode(true);
        } else {
            echo json_encode(false);
        }
        exit;
    }

    public function makes($action = NULL, $id = NULL) {
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $newEntity = $this->Makes->newEntity();
            $newEntity->id = $id;
            $patchEntity = $this->Makes->patchEntity($newEntity, $data);
            if ($this->Makes->save($patchEntity)) {
                $msg = !empty($id) ? 'updated' : 'created';
                $this->Flash->success(__("Make has been $msg successfully."));
                $this->redirect(HTTP_ROOT . 'admin/appadmins/makes');
            } else {
                $this->Flash->error(__("Some error occured, try later."));
                $this->redirect($this->referer());
            }
        }
        if (!empty($id)) {
            $editValue = $this->Makes->get($id);
        }
        $makes = $this->Makes->find();
        $this->set(compact('makes', 'editValue'));
    }

    public function models($action = NULL, $id = NULL) {
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $newEntity = $this->Models->newEntity();
            $newEntity->id = $id;
            $patchEntity = $this->Models->patchEntity($newEntity, $data);
            if ($this->Models->save($patchEntity)) {
                $msg = !empty($id) ? 'updated' : 'created';
                $this->Flash->success(__("Model has been $msg successfully."));
                $this->redirect(HTTP_ROOT . 'admin/appadmins/models');
            } else {
                $this->Flash->error(__("Some error occured, try later."));
                $this->redirect($this->referer());
            }
        }
        if (!empty($id)) {
            $editValue = $this->Models->get($id);
        }
        $makeLists = $this->Makes->find('list', ['keyField' => 'id', 'valueField' => 'name'])->toArray();
        $models = $this->Models->find()->contain(['Makes']);
        $this->set(compact('makeLists', 'models', 'editValue'));
    }

    public function bodyTypes($action = NULL, $id = NULL) {
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $newEntity = $this->BodyTypes->newEntity();
            $newEntity->id = $id;
            $patchEntity = $this->BodyTypes->patchEntity($newEntity, $data);
            if ($this->BodyTypes->save($patchEntity)) {
                $msg = !empty($id) ? 'updated' : 'created';
                $this->Flash->success(__("Body Type has been $msg successfully."));
                $this->redirect(HTTP_ROOT . 'admin/appadmins/body-types');
            } else {
                $this->Flash->error(__("Some error occured, try later."));
                $this->redirect($this->referer());
            }
        }
        if (!empty($id)) {
            $editValue = $this->BodyTypes->get($id);
        }
        $bodyTypes = $this->BodyTypes->find();
        $this->set(compact('bodyTypes', 'editValue'));
    }

    public function CarStatus($action = NULL, $id = NULL) {
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $newEntity = $this->CarStatus->newEntity();
            $newEntity->id = $id;
            $newEntity->choose_car_avail = (isset($data['choose_car_avail'])) ? 1 : 0;
            $newEntity->not_sure_avail = (isset($data['not_sure_avail'])) ? 1 : 0;
            if (empty($id)) {
                $newEntity->created = date('Y-m-d H:i:s');
            }
            $newEntity->modified = date('Y-m-d H:i:s');
            $patchEntity = $this->CarStatus->patchEntity($newEntity, $data);
            if ($this->CarStatus->save($patchEntity)) {
                $msg = !empty($id) ? 'updated' : 'created';
                $this->Flash->success(__("Car Status has been $msg successfully."));
                $this->redirect(HTTP_ROOT . 'admin/appadmins/car-status');
            } else {
                $this->Flash->error(__("Some error occured, try later."));
                $this->redirect($this->referer());
            }
        }
        if (!empty($id)) {
            $editValue = $this->CarStatus->get($id);
        }
        $carStatus = $this->CarStatus->find();
        $this->set(compact('carStatus', 'editValue'));
    }

    public function fuels($action = NULL, $id = NULL) {
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $newEntity = $this->Fuels->newEntity();
            $newEntity->id = $id;
            $newEntity->name = $data['name'];
//            $patchEntity = $this->Fuels->patchEntity($newEntity, $data);
            if ($this->Fuels->save($newEntity)) {
                $msg = !empty($id) ? 'updated' : 'created';
                $this->Flash->success(__("Fuel has been $msg successfully."));
                $this->redirect($this->referer());
            } else {
                $this->Flash->error(__("Some error occured, try later."));
                $this->redirect($this->referer());
            }
        }
        if (!empty($id)) {
            $editValue = $this->Fuels->get($id);
        }
        $fuels = $this->Fuels->find();
        $this->set(compact('fuels', 'editValue'));
    }

    public function transmissions($action = NULL, $id = NULL) {
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $newEntity = $this->Transmissions->newEntity();
            $newEntity->id = $id;
            $newEntity->name = $data['name'];
//            $patchEntity = $this->Transmissions->patchEntity($newEntity, $data);
            if ($this->Transmissions->save($newEntity)) {
                $msg = !empty($id) ? 'updated' : 'created';
                $this->Flash->success(__("Transmission has been $msg successfully."));
                $this->redirect($this->referer());
            } else {
                $this->Flash->error(__("Some error occured, try later."));
                $this->redirect($this->referer());
            }
        }
        if (!empty($id)) {
            $editValue = $this->Transmissions->get($id);
        }
        $transmissions = $this->Transmissions->find();
        $this->set(compact('transmissions', 'editValue'));
    }

    public function towns($action = NULL, $id = NULL) {
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $newEntity = $this->Towns->newEntity();
            $newEntity->id = $id;
            $newEntity->name = $data['name'];
//            $patchEntity = $this->Towns->patchEntity($newEntity, $data);
            if ($this->Towns->save($newEntity)) {
                $msg = !empty($id) ? 'updated' : 'created';
                $this->Flash->success(__("Town has been $msg successfully."));
                $this->redirect($this->referer());
            } else {
                $this->Flash->error(__("Some error occured, try later."));
                $this->redirect($this->referer());
            }
        }
        if (!empty($id)) {
            $editValue = $this->Towns->get($id);
        }
        $towns = $this->Towns->find();
        $this->set(compact('towns', 'editValue'));
    }

    public function banks($action = NULL, $id = NULL) {
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $bank = $this->Banks->newEntity();
            $bank->id = $id;
            $bank->name = $data['name'];
            $bank->email = $data['email'];
            $bank->phone = $data['phone'];
            if (!empty($data['logo']['tmp_name'])) {
                $logo = $this->Custom->uploadImageByWidthHeight($data['logo']['tmp_name'], $data['logo']['name'], BANK_LOGO, 330, 230);
                $bank->logo = $logo;
            }
            if ($this->Banks->save($bank)) {
                $msg = !empty($id) ? 'updated' : 'created';
                $this->Flash->success(__("Bank has been $msg successfully."));
                $this->redirect($this->referer());
            } else {
                $this->Flash->error(__("Some error occured, try later."));
                $this->redirect($this->referer());
            }
        }
        if (!empty($id)) {
            $editValue = $this->Banks->get($id);
        }
        $banks = $this->Banks->find();
        $this->set(compact('banks', 'editValue'));
    }

    public function visitors() {
        $this->loadModel('Visitors');
        $visitors = $this->Visitors->find();
        $this->set(compact('visitors'));
    }

    public function deleteBankLogo($logo) {
        unlink(WWW_ROOT . BANK_LOGO . $logo);
        $this->Banks->query()->update()->set(['logo' => ''])->where(['logo' => $logo])->execute();
        $this->Flash->success(__('Logo been deleted successfully'));
        $this->redirect($this->referer());
    }

    public function deactive($id = null, $table = null, $type = null) {
        if ($this->$table->query()->update()->set(['status' => 0])->where(['id' => $id])->execute()) {
            if ($table == 'Makes') {
                $this->Flash->success(__('Make has been deactivated.'));
                $this->redirect($this->referer());
            } else if ($table == 'Models') {
                $this->Flash->success(__('Model has been deactivated.'));
                $this->redirect($this->referer());
            } else if ($table == 'Fuels') {
                $this->Flash->success(__('Fuel has been deactivated.'));
                $this->redirect($this->referer());
            } else if ($table == 'BodyTypes') {
                $this->Flash->success(__('Body Type has been deactivated.'));
                $this->redirect($this->referer());
            } else if ($table == 'Transmissions') {
                $this->Flash->success(__('Transmission has been deactivated.'));
                $this->redirect($this->referer());
            } else if ($table == 'Towns') {
                $this->Flash->success(__('Town has been deactivated.'));
                $this->redirect($this->referer());
            } else if ($table == 'Users') {
                $this->Flash->success(__("This $type has been deactivated."));
                $this->redirect($this->referer());
            }
        }
    }

    public function active($id = null, $table = null, $type = null) {
        if ($this->$table->query()->update()->set(['status' => 1])->where(['id' => $id])->execute()) {
            if ($table == 'Makes') {
                $this->Flash->success(__('Make has been activated.'));
                $this->redirect($this->referer());
            } else if ($table == 'Models') {
                $this->Flash->success(__('Model has been activated.'));
                $this->redirect($this->referer());
            } else if ($table == 'Fuels') {
                $this->Flash->success(__('Fuels has been activated.'));
                $this->redirect($this->referer());
            } else if ($table == 'BodyTypes') {
                $this->Flash->success(__('Body Type has been activated.'));
                $this->redirect($this->referer());
            } else if ($table == 'Transmissions') {
                $this->Flash->success(__('Transmission has been activated.'));
                $this->redirect($this->referer());
            } else if ($table == 'Towns') {
                $this->Flash->success(__('Town has been activated.'));
                $this->redirect($this->referer());
            } else if ($table == 'Users') {
                $this->Flash->success(__("This $type has been activated."));
                $this->redirect($this->referer());
            }
        }
    }

//////////  Delete For All /////////

    public function delete($id = null, $table = null) {
        $getDetail = $this->$table->find('all')->where([$table . '.id' => $id])->first();

        if ($table == 'Users') {
            $userDetail = $this->AdminDetails->find()->where(['user_id' => $id])->first();
            if ($userDetail) {
                $data = $this->AdminDetails->get($userDetail->id);
                $this->AdminDetails->delete($data);
            }
            $data = $this->$table->get($id);
            $this->$table->delete($data);
            $this->Flash->success(__(' This admin has been deleted.'));
            return $this->redirect(HTTP_ROOT . 'admin/admin-listing');
        }
        if ($table == 'Makes') {
            $chkModelsAvailable = $this->Models->find()->where(['Models.make_id' => $id]);
            if ($chkModelsAvailable->count()) {
                $this->Flash->error(__('' . $getDetail->name . ' has been used by one or more models.'));
            } else {
                $data = $this->$table->get($id);
                $this->$table->delete($data);
                $this->Flash->success(__('' . $getDetail->name . ' has been deleted.'));
            }
            return $this->redirect(HTTP_ROOT . 'admin/appadmins/makes');
        }

        $data = $this->$table->get($id);
        $dataDelete = $this->$table->delete($data);
        if ($dataDelete) {
            if ($table == 'Models') {
                $this->Flash->success(__('' . $getDetail->name . ' has been deleted.'));
                return $this->redirect(HTTP_ROOT . 'admin/appadmins/models');
            } else if ($table == 'Fuels') {
                $this->Flash->success(__('' . $getDetail->name . ' has been deleted.'));
                return $this->redirect(HTTP_ROOT . 'admin/appadmins/fuels');
            } else if ($table == 'BodyTypes') {
                $this->Flash->success(__('' . $getDetail->name . ' has been deleted.'));
                return $this->redirect(HTTP_ROOT . 'admin/appadmins/body-types');
            } else if ($table == 'Transmissions') {
                $this->Flash->success(__('' . $getDetail->name . ' has been deleted.'));
                return $this->redirect(HTTP_ROOT . 'admin/appadmins/transmissions');
            } else if ($table == 'Towns') {
                $this->Flash->success(__('' . $getDetail->name . ' has been deleted.'));
                return $this->redirect(HTTP_ROOT . 'admin/appadmins/towns');
            } else if ($table == 'Banks') {
                unlink(WWW_ROOT . BANK_LOGO . $getDetail->logo);
                $this->Flash->success(__('' . $getDetail->event_name . ' has been deleted.'));
                return $this->redirect(HTTP_ROOT . 'admin/appadmins/banks');
            } else if ($table == 'CarStatus') {
                $this->Flash->success(__('' . $getDetail->name . ' has been deleted.'));
                return $this->redirect(HTTP_ROOT . 'admin/appadmins/car-status');
            }
        }
    }

    public function valuesSetting() {
        $this->loadModel('AdminSettings');
        $adminSetting = $this->AdminSettings->get(1);
        if ($this->request->is(['post', 'put'])) {
            $adminSetting = $this->AdminSettings->patchEntity($adminSetting, $this->request->data);
            if ($this->AdminSettings->save($adminSetting)) {
                //Update Config File//
                /*
                  $getAdminSettingsDetails = $this->AdminSettings->get(1)->toArray();
                  $config = '<?php ';
                  foreach ($getAdminSettingsDetails as $key => $val) {
                  $value = addslashes($val);
                  $key = strtoupper($key);
                  $config .= "\n define('{$key}', '{$value}');";
                  }
                  $config .= "\n define('PAGE_LIMIT','10'); ";
                  $config .= "\n define('ADMIN_PAGE_LIMIT','20'); ";
                  $config .= "\n define('SITE_URL', '" . HTTP_ROOT . "'); \n";
                  $config .= '?>';
                  $file = ROOT . DS . 'config' . DS . 'config.php';
                  file_put_contents($file, $config);
                 */
                //Show Success Message//
                $this->Flash->success(__('Values are updated successfully.'));
                $this->redirect($this->referer());
            } else {
                $this->Flash->error(__('Error Occcured!!'));
            }
        }
        $getAdminSettings = $this->AdminSettings->get(1)->toArray();
//        pr($getAdminSettings);
        $this->set(compact('getAdminSettings', 'adminSetting'));
    }

///////////Admin Mail Part Starts Here\\\\\\\\\\\\\\\
    public function allmailformats() {
        $mailFormats = $this->MailTemplates->find('all', ['order' => 'MailTemplates.id DESC'])->where(['MailTemplates.is_active' => 1]);
        $this->set(compact('mailFormats'));
    }

    public function editmailformats($id) {

        $editMailFormats = $this->MailTemplates->get($id, ['contain' => []]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $set = $this->MailTemplates->patchEntity($editMailFormats, $this->request->data);
            if ($this->MailTemplates->save($set)) {
                $this->Flash->success(__('MailTemplates updated successfully'));
                return $this->redirect(['action' => 'allmailformats']);
            } else {
                $this->Flash->error(__('Mail formats not updated successfully, try again.'));
            }
        }
        $this->set(compact('editMailFormats'));
        $this->set('_serialize', ['editMailFormats']);
    }

    public function valuesetting() {
        if (($this->request->session()->read('Auth.User.id') == 1)) {
            $settings = $this->Settings->find('all', ['order' => 'Settings.id DESC'])->where(['Settings.type' => 1, 'Settings.is_active' => 1]);
            $this->set(compact('settings'));
            if ($this->request->is(['patch', 'post', 'put'])) {
                $set = $this->request->data;
                $count = 0;
                foreach ($set as $key => $value) {
                    $condition = array('name' => $key);
                    $this->Settings->updateAll(['value' => $value], ['name' => $key]);
                    $count++;
                }
                $this->Flash->success(__('Value Setting updated successfully.'));
                $this->redirect(HTTP_ROOT . 'admin/appadmins/valuesetting/');
            }
        } else {
            return $this->redirect(['controller' => 'users', 'action' => 'notaccess']);
        }
    }

///////////Admin Mail Part Ends Here\\\\\\\\\\\\\\\
///////////Admin Profile Section, password and logout section Starts\\\\\\\\\\\\\\\
    public function profile() {
        $query = $this->Users->find('all')->contain(['AdminDetails'])->where(['Users.id' => $this->Auth->user('id')], ['Users.is_active' => 1]);
        $userDetail = $query->first();
        $this->set(compact('userDetail'));
    }

    public function editprofile() {
        $authUserId = $this->Auth->user('id');
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
//            if (!empty($data['profile_image']['name'])) {
//                $profileImage = $this->Custom->uploadThumbImage($data['profile_image']['tmp_name'], $data['profile_image']['name'], USER_PROFILE_IMAGE, 280, 280);
//                if ($profileImage) {
//                    $this->Users->query()->update()->set(['profile_image' => $profileImage])->where(['id' => 1])->execute();
//                }
//            }
//            unset($data['profile_image']);        

            if ($this->AdminDetails->query()->update()->set(['firstname' => $data['firstname'], 'lastname' => $data['lastname']])->where(['user_id' => $authUserId])->execute()) {

                $this->Flash->success(__('Your profile details has been updated successfully'));
                return $this->redirect(['action' => 'profile']);
            } else {
                $this->Flash->success(__('Some Error Occured.Please try Again!!'));
            }
        }
        $query = $this->Users->find('all')->contain(['AdminDetails'])->where(['Users.id' => $authUserId], ['Users.is_active' => 1]);
        $userDetail = $query->first();
        $this->set(compact('userDetail'));
    }

    public function passwordsetting() {
        $user = $this->Users->get($this->Auth->user('id'));
        if (!empty($this->request->data)) {
            $user = $this->Users->patchEntity($user, ['old_password' => $this->request->data['old_password'],
                'password' => $this->request->data['password1'],
                'password1' => $this->request->data['password1'],
                'password2' => $this->request->data['password2']], ['validate' => 'password']);
            if ($this->Users->save($user)) {
                $this->Flash->success('The password is successfully changed');
                $this->redirect(HTTP_ROOT . 'admin/appadmins/passwordsetting');
            } else {
                $this->Flash->error('There was an error during the update password!');
            }
        }
        $this->set('user', $user);
    }

///////////Admin Profile Section, password and logout section Ends\\\\\\\\\\\\\\\
}
