<?php

//Something to write to txt log
$result[0]['success'] = 1;
$username = 'prakash';
$password = 123456;
$log = "User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a") . PHP_EOL .
        "Attempt: " . ($result[0]['success'] == '1' ? 'Success' : 'Failed') . PHP_EOL .
        "User: " . $username . PHP_EOL .
        "Pass: " . $password . PHP_EOL .
        "-------------------------" . PHP_EOL;
//Save string to log, use FILE_APPEND to append.
if (file_put_contents('./log_' . date("j.n.Y") . '.txt', $log, FILE_APPEND)) {
    echo 'Log file created successfully';
} else {
    echo 'Problem occured on log file.';
}

function hasAccess($username, $password) {
    $form = array();
    $form['username'] = $username;
    $form['password'] = $password;

    $securityDAO = $this->getDAO('SecurityDAO');
    $result = $securityDAO->hasAccess($form);

    //Write action to txt log
    $log = "User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a") . PHP_EOL .
            "Attempt: " . ($result[0]['success'] == '1' ? 'Success' : 'Failed') . PHP_EOL .
            "User: " . $username . PHP_EOL .
            "Pass: " . $password . PHP_EOL .
            "-------------------------" . PHP_EOL;
    //-
    file_put_contents('./log_' . date("j.n.Y") . '.txt', $log, FILE_APPEND);

    if ($result[0]['success'] == '1') {
        $this->Session->add('user_id', $result[0]['id']);
        //$this->Session->add('username', $result[0]['username']);
        //$this->Session->add('roleid', $result[0]['roleid']);
        return $this->status(0, true, 'auth.success', $result);
    } else {
        return $this->status(0, false, 'auth.failed', $result);
    }
}
