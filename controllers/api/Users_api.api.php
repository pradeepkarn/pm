<?php

use PHPMailer\PHPMailer\PHPMailer;

class Users_api
{
    public $get;
    public $post;
    public $files;
    public $db;
    function __construct()
    {
        $this->db = (new DB_ctrl)->db;
        $this->post = obj($_POST);
        $this->get = obj($_GET);
        $this->files = isset($_FILES) ? obj($_FILES) : null;
    }
    function login($req = null)
    {
        header('Content-Type: application/json');
        $ok = true;
        $req = obj($req);
        $data  = json_decode(file_get_contents('php://input'));
        if (isset($req->ug)) {
            if (!in_array($req->ug, USER_GROUP_LIST)) {
                $ok = false;
                msg_set("Invalid account group");
            }
        } else {
            $ok = false;
            msg_set("No user group provided");
        }
        if (!$ok) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        $rules = [
            'credit' => 'required|string',
            'password' => 'required|string'
        ];

        $pass = validateData(data: arr($data), rules: $rules);
        if (!$pass) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        $user = null;
        $this->db->tableName = "pk_user";
        if (!$user) {
            $arr['username'] = $data->credit;
            $arr['password'] = md5($data->password);
            $user = $this->db->findOne($arr);
            $arr = null;
        }
        if (!$user) {
            $arr['email'] = $data->credit;
            $arr['password'] = md5($data->password);
            $user = $this->db->findOne($arr);
            $arr = null;
        }
        if (!$user) {
            msg_set("Invalid credential");
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        if ($user['is_active'] == 0) {
            msg_set("Your account is inactive");
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        if ($user) {
            if ($user['user_group'] != $req->ug) {
                $ok = false;
                msg_set("Invalid login portal");
                $api['success'] = false;
                $api['data'] = null;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            }
            $after_second = 10 * 60;
            $app_login_time = strtotime($user['app_login_time'] ?? date('Y-m-d H:i:s'));
            $time_out = $after_second + $app_login_time;
            $current_time = strtotime(date('Y-m-d H:i:s'));
            if ($current_time > $time_out) {
                $token = uniqid() . bin2hex(random_bytes(8)) . "u" . $user['id'];
                $datetime = date('Y-m-d H:i:s');
                $this->db->tableName = 'pk_user';
                $this->db->insertData = array('app_login_token' => $token, 'app_login_time' => $datetime);
                $this->db->pk($user['id']);
                $this->db->update();
                $user = $this->get_user_by_id($id = $user['id']);
                msg_set("Login success, token refreshed");
                $api['success'] = true;
                $api['data'] = $user;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            } else {
                $user = $this->get_user_by_id($id = $user['id']);
                msg_set("Login success");
                $api['success'] = true;
                $api['data'] = $user;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            }
        } else {
            msg_set("Invalid credential");
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
    }
    function login_via_token($req = null)
    {
        header('Content-Type: application/json');
        $ok = true;
        $req = obj($req);
        $data  = json_decode(file_get_contents('php://input'));
        if (isset($req->ug)) {
            if (!in_array($req->ug, USER_GROUP_LIST)) {
                $ok = false;
                msg_set("Invalid account group");
            }
        } else {
            $ok = false;
            msg_set("No user group provided");
        }
        if (!$ok) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        $rules = [
            'token' => 'required|string'
        ];

        $pass = validateData(data: arr($data), rules: $rules);
        if (!$pass) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        $user = false;
        $user = $this->get_user_by_token($data->token);

        if ($user) {
            if ($user['is_active'] == 0) {
                msg_set("Your account is inactive");
                $api['success'] = false;
                $api['data'] = null;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            }
            if ($user['user_group'] != $req->ug) {
                $ok = false;
                msg_set("Invalid login portal");
                $api['success'] = false;
                $api['data'] = null;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            }
            msg_set("Login success");
            $api['success'] = true;
            $api['data'] = $user;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        } else {
            msg_set("Invalid token");
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
    }
    function create_account($req = null)
    {
        header('Content-Type: application/json');
        $ok = true;
        $req = obj($req);
        $data  = $_POST;
        $data['image'] = $_FILES['image'] ?? null;
        $data['vhcl_doc'] = $_FILES['vhcl_doc'] ?? null;
        $data['dl_doc'] = $_FILES['dl_doc'] ?? null;
        $data['nid_doc'] = $_FILES['nid_doc'] ?? null;

        if (isset($req->ug)) {
            if (!in_array($req->ug, USER_GROUP_LIST)) {
                $ok = false;
                msg_set("Invalid account group");
            }
        } else {
            $ok = false;
            msg_set("No user group provided");
        }
        if (!$ok) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        $rules = [
            'email' => 'required|email',
            // 'username' => 'required|string|min:4|max:16',
            // 'image' => 'required|file',
            'first_name' => 'required|string',
            'password' => 'required|string'
        ];
        // if ($req->ug == 'driver') {
        //     $rules_driver = [
        //         'dl_doc' => 'required|file',
        //         'nid_doc' => 'required|file',
        //         'vhcl_doc' => 'required|file',
        //         'dl_no' => 'required|string',
        //         'nid_no' => 'required|string',
        //         'vhcl_no' => 'required|string',
        //     ];
        //     $rules = array_merge($rules, $rules_driver);
        // }
        $pass = validateData(data: $data, rules: $rules);
        if (!$pass) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }

        $request = obj($data);
        $request->username = $request->username ?? uniqid();
        $this->db = $this->db;
        $pdo = $this->db->conn;
        $pdo->beginTransaction();
        $this->db->tableName = 'pk_user';
        $username = generate_clean_username($request->username);
        $username_exists = $this->db->get(['username' => $username]);
        $email_exists = $this->db->get(['email' => $request->email]);
        // $mobile_exists = $this->db->get(['mobile' => $request->mobile]);
        if ($username_exists) {
            $_SESSION['msg'][] = 'Usernam not availble please try with another username';
            $ok = false;
        }
        if ($email_exists) {
            $_SESSION['msg'][] = 'Email is already exists';
            $ok = false;
        }
        // if ($mobile_exists) {
        //     $_SESSION['msg'][] = 'Mobile is registered';
        //     $ok = false;
        // }
        if (!$ok) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        if (isset($request->email)) {
            $arr = null;
            $arr['user_group'] = $req->ug;
            $arr['email'] = $request->email;
            $arr['username'] = $username;
            $arr['first_name'] = $request->first_name;
            $arr['last_name'] = $request->last_name ?? null;
            $arr['isd_code'] = intval($request?->isd_code) ?? null;
            $arr['mobile'] = intval($request?->mobile) ?? null;
            $arr['password'] = md5($request->password);
            $arr['nid_no'] = sanitize_remove_tags($request->nid_no ?? null);
            $arr['dl_no'] = sanitize_remove_tags($request->dl_no ?? null);
            $arr['vhcl_no'] = sanitize_remove_tags($request->vhcl_no ?? null);
            if (isset($request->bio)) {
                $arr['bio'] = $request->bio;
            }
            $arr['created_at'] = date('Y-m-d H:i:s');
            $this->db->tableName = 'pk_user';
            $this->db->insertData = $arr;
            try {
                $userid = $this->db->create();
                $filearr = $this->upload_files($userid, $request);
                if ($filearr) {
                    $this->db->pk($userid);
                    $this->db->insertData = $filearr;
                    $this->db->update();
                }
                msg_set('Account created');
                $ok = true;
                $pdo->commit();
            } catch (PDOException $th) {
                $pdo->rollBack();
                msg_set('Account not created');
                $ok = false;
            }
        } else {
            $pdo->rollBack();
            msg_set('Missing required field, uaser not created');
            $ok = false;
        }
        if (!$ok) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        } else {
            $api['success'] = true;
            $api['data'] = [];
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
    }
    function update_account($req = null)
    {
        header('Content-Type: application/json');
        $ok = true;
        $req = obj($req);
        $data  = $_POST;
        $data['image'] = $_FILES['image'] ?? null;


        if (isset($req->ug)) {
            if (!in_array($req->ug, USER_GROUP_LIST)) {
                $ok = false;
                msg_set("Invalid account group");
            }
        } else {
            $ok = false;
            msg_set("No user group provided");
        }
        if (!$ok) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        $rules = [
            'token' => 'required|string'
        ];

        $pass = validateData(data: $data, rules: $rules);
        if (!$pass) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }

        $request = obj($data);
        $user = $this->get_user_by_token($request->token);
        if (!$user) {
            msg_set("Invalid token");
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        $user = obj($user);
        // $request->username = $user->username;
        $this->db = $this->db;
        $pdo = $this->db->conn;
        $pdo->beginTransaction();
        $this->db->tableName = 'pk_user';

        if (!$ok) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        if (isset($user)) {
            $arr = null;
            $arr['first_name'] = $request->first_name ?? $user->first_name;
            $arr['last_name'] = $request->last_name ?? $user->last_name;
            if (isset($request->password)) {
                $arr['password'] = md5($request->password);
            }

            if (isset($request->bio)) {
                $arr['bio'] = $request->bio;
            }
            $arr['created_at'] = date('Y-m-d H:i:s');
            $this->db->tableName = 'pk_user';
            $this->db->insertData = $arr;
            try {
                $this->db->pk($user->id);
                $this->db->update();
                $request->username = $user->username;
                if (isset($_FILES['image'])) {
                    $filearr = $this->upload_files($user->id, $request);
                    if ($filearr) {
                        $this->db->pk($user->id);
                        $this->db->insertData = $filearr;
                        $this->db->update();
                    }
                }

                msg_set('Account created');
                $ok = true;
                $pdo->commit();
            } catch (PDOException $th) {
                $pdo->rollBack();
                msg_set('Account not created');
                $ok = false;
            }
        } else {
            $pdo->rollBack();
            msg_set('Missing required field, uaser not created');
            $ok = false;
        }
        if (!$ok) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        } else {
            $api['success'] = true;
            $api['data'] = $this->get_user_by_token($request->token);
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
    }
    function generate_temp_password($req = null)
    {
        header('Content-Type: application/json');
        $ok = true;
        $req = obj($req);
        $data  = json_decode(file_get_contents("php://input"), true);
        if (isset($req->ug)) {
            if (!in_array($req->ug, USER_GROUP_LIST)) {
                $ok = false;
                msg_set("Invalid account group");
            }
        } else {
            $ok = false;
            msg_set("No user group provided");
        }
        if (!$ok) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        $rules = [
            'email' => 'required|email'
        ];

        $pass = validateData(data: $data, rules: $rules);
        if (!$pass) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }

        $request = obj($data);
        $valid_email = email_has_valid_dns($request->email);
        if (!$valid_email) {
            msg_set("Your email is not valid");
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        } else {
            $this->db->tableName = 'pk_user';
            $user = $this->db->findOne(['email' => $request->email]);
            if (!$user) {
                msg_set("This email does not exists in our database");
                $api['success'] = false;
                $api['data'] = null;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            } else {
                try {
                    $randpass = random_int(100000, 999999);
                    $mail = php_mailer(new PHPMailer());
                    $mail->setFrom(email, SITE_NAME . " Temporary Password");
                    $mail->isHTML(true);
                    $mail->Subject = 'Password';
                    $mailObj = obj([
                        'password' => $randpass
                    ]);
                    $body = render_template("emails/password_reset/temp-pass.php", $mailObj);
                    $mail->Body = $body;
                    $mail->addAddress($request->email, "{$user['first_name']}");
                    $mail->send();
                    $this->db->insertData['password'] = md5($randpass);
                    $this->db->update();
                    $data['msg'] = "A temporary password has been sent to $request->email, please check.";
                    $data['success'] = true;
                    $data['data'] = obj([]);
                    echo json_encode($data);
                    exit;
                } catch (ErrorException $e) {
                    $data['msg'] = "Email not sent";
                    $data['success'] = false;
                    $data['data'] = null;
                    echo json_encode($data);
                    exit;
                }
            }
        }

        $user = $this->get_user_by_token($request->token);
        if (!$user) {
            msg_set("Invalid token");
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        $user = obj($user);
        // $request->username = $user->username;
        $this->db = $this->db;
        $pdo = $this->db->conn;
        $pdo->beginTransaction();
        $this->db->tableName = 'pk_user';

        if (!$ok) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        if (isset($user)) {
            $arr = null;
            $arr['first_name'] = $request->first_name ?? $user->first_name;
            $arr['last_name'] = $request->last_name ?? $user->last_name;
            if (isset($request->password)) {
                $arr['password'] = md5($request->password);
            }

            if (isset($request->bio)) {
                $arr['bio'] = $request->bio;
            }
            $arr['created_at'] = date('Y-m-d H:i:s');
            $this->db->tableName = 'pk_user';
            $this->db->insertData = $arr;
            try {
                $this->db->pk($user->id);
                $this->db->update();
                $request->username = $user->username;
                if (isset($_FILES['image'])) {
                    $filearr = $this->upload_files($user->id, $request);
                    if ($filearr) {
                        $this->db->pk($user->id);
                        $this->db->insertData = $filearr;
                        $this->db->update();
                    }
                }

                msg_set('Account created');
                $ok = true;
                $pdo->commit();
            } catch (PDOException $th) {
                $pdo->rollBack();
                msg_set('Account not created');
                $ok = false;
            }
        } else {
            $pdo->rollBack();
            msg_set('Missing required field, uaser not created');
            $ok = false;
        }
        if (!$ok) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        } else {
            $api['success'] = true;
            $api['data'] = $this->get_user_by_token($request->token);
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
    }

    function upload_files($userid, $request)
    {
        $filearr = null;
        if (intval($userid)) {
            $user = $this->get_user_by_id($userid);
            $old = $user ? obj($user) : null;
            if (isset($request->image) && $request->image['name'] != "" && $request->image['error'] == 0) {
                $ext = pathinfo($request->image['name'], PATHINFO_EXTENSION);
                $imgname = str_replace(" ", "_", getUrlSafeString($request->username)) . uniqid("_") . "." . $ext;
                $dir = MEDIA_ROOT . "images/profiles/" . $imgname;
                $upload = move_uploaded_file($request->image['tmp_name'], $dir);
                if ($upload) {
                    $arr['image'] = $imgname;
                    if ($old) {
                        if ($old->image != "") {
                            $olddir = MEDIA_ROOT . "images/profiles/" . $old->image;
                            if (file_exists($olddir)) {
                                unlink($olddir);
                            }
                        }
                    }
                    $filearr['image'] = $imgname;
                }
            }
            if (isset($request->nid_doc) && $request->nid_doc['name'] != "" && $request->nid_doc['error'] == 0) {
                $ext = pathinfo($request->nid_doc['name'], PATHINFO_EXTENSION);
                $docname = str_replace(" ", "_", getUrlSafeString($request->username)) . uniqid("_") . "." . $ext;
                $dir = MEDIA_ROOT . "docs/" . $docname;
                $upload = move_uploaded_file($request->nid_doc['tmp_name'], $dir);
                if ($upload) {
                    $arr['nid_doc'] = $docname;
                    if ($old) {
                        if ($old->image != "") {
                            $olddir = MEDIA_ROOT . "docs/" . $old->nid_doc;
                            if (file_exists($olddir)) {
                                unlink($olddir);
                            }
                        }
                    }
                    $filearr['nid_doc'] = $docname;
                }
            }
            if (isset($request->dl_doc) && $request->dl_doc['name'] != "" && $request->dl_doc['error'] == 0) {
                $ext = pathinfo($request->dl_doc['name'], PATHINFO_EXTENSION);
                $docname = str_replace(" ", "_", getUrlSafeString($request->username)) . uniqid("_") . "." . $ext;
                $dir = MEDIA_ROOT . "docs/" . $docname;
                $upload = move_uploaded_file($request->dl_doc['tmp_name'], $dir);
                if ($upload) {
                    $arr['dl_doc'] = $docname;
                    if ($old) {
                        if ($old->image != "") {
                            $olddir = MEDIA_ROOT . "docs/" . $old->dl_doc;
                            if (file_exists($olddir)) {
                                unlink($olddir);
                            }
                        }
                    }
                    $filearr['dl_doc'] = $imgname;
                }
            }
            if (isset($request->vhcl_doc) && $request->vhcl_doc['name'] != "" && $request->vhcl_doc['error'] == 0) {
                $ext = pathinfo($request->vhcl_doc['name'], PATHINFO_EXTENSION);
                $docname = str_replace(" ", "_", getUrlSafeString($request->username)) . uniqid("_") . "." . $ext;
                $dir = MEDIA_ROOT . "docs/" . $docname;
                $upload = move_uploaded_file($request->vhcl_doc['tmp_name'], $dir);
                if ($upload) {
                    $arr['vhcl_doc'] = $docname;
                    if ($old) {
                        if ($old->image != "") {
                            $olddir = MEDIA_ROOT . "docs/" . $old->vhcl_doc;
                            if (file_exists($olddir)) {
                                unlink($olddir);
                            }
                        }
                    }
                    $filearr['vhcl_doc'] = $docname;
                }
            }
            return $filearr;
        } else {
            return false;
        }
    }

    function get_user_by_id($id = null)
    {
        if ($id) {
            $u = $this->db->showOne("select * from pk_user where id = $id");
            if ($u) {
                $u = obj($u);
                return array(
                    'id' => strval($u->id),
                    'user_group' => $u->user_group,
                    'username' => strval($u->username),
                    'first_name' => $u->first_name,
                    'last_name' => $u->last_name,
                    'image' => dp_or_null($u->image),
                    'email' => $u->email,
                    'isd_code' => $u->isd_code,
                    'mobile' => $u->mobile,
                    'is_online' => $u->is_online,
                    'token' => $u->app_login_token,
                    'is_active' => $u->is_active
                );
            }
        }
        return false;
    }
    function get_user_by_token($token = null)
    {
        if ($token) {
            $u = $this->db->showOne("select * from pk_user where app_login_token = '$token'");
            if ($u) {
                $u = obj($u);
                return array(
                    'id' => strval($u->id),
                    'user_group' => $u->user_group,
                    'username' => strval($u->username),
                    'first_name' => $u->first_name,
                    'last_name' => $u->last_name,
                    'image' => dp_or_null($u->image),
                    'email' => $u->email,
                    'isd_code' => $u->isd_code,
                    'mobile' => $u->mobile,
                    'is_online' => $u->is_online == 1 ? true : false,
                    'token' => $u->app_login_token,
                    'is_active' => $u->is_active
                );
            }
        }
        return false;
    }
    function set_user_online($req = null)
    {
        header('Content-Type: application/json');
        $req = obj($req);
        $data  = json_decode(file_get_contents('php://input'));

        $rules = [
            'token' => 'required|string',
            'is_online' => 'required|bool'
        ];

        $pass = validateData(data: arr($data), rules: $rules);
        if (!$pass) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        $user = false;
        $user = (new Users_api)->get_user_by_token($data->token);
        if ($user) {
            if ($user['user_group'] != 'driver') {
                $ok = false;
                msg_set("Invalid login portal");
                $api['success'] = false;
                $api['data'] = null;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            }

            try {
                $is_online = $data->is_online == "true" ? 1 : 0;
                $this->db->tableName = 'pk_user';
                $this->db->pk($user['id']);
                $this->db->insertData['is_online'] = $is_online;
                $rpl = $this->db->update();
                msg_set($rpl ? "State changed to $data->is_online" : "State not changed");
                $api['success'] = $rpl ? true : false;
                $api['data'] =  $rpl ? [] : null;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            } catch (PDOException $th) {
                // echo $th;
                msg_set("Unable to change state");
                $api['success'] = false;
                $api['data'] = null;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            }
        } else {
            msg_set("User not found, invalid token");
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
    }
}
