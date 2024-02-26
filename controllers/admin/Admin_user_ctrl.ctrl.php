<?php
class Admin_user_ctrl
{
    // Cretae page
    public function create($req = null)
    {
        $req = obj($req);
        $context = (object) array(
            'page' => 'users/create.php',
            'data' => (object) array(
                'req' => obj($req),
                'cat_list' => $this->user_list(user_group: $req->ug, limit: 1000)
            )
        );
        $this->render_main($context);
    }
    // List users
    public function list($req = null)
    {
        $req = obj($req);
        $current_page = 0;
        $data_limit = DB_ROW_LIMIT;
        $page_limit = "0,$data_limit";
        $cp = 0;
        if (isset($req->page) && intval($req->page)) {
            $cp = $req->page;
            $current_page = (abs($req->page) - 1) * $data_limit;
            $page_limit = "$current_page,$data_limit";
        }
        $total_user = $this->user_list(user_group: $req->ug, ord: "DESC", limit: 10000, active: 1);
        $tu = count($total_user);
        if ($tu %  $data_limit == 0) {
            $tu = $tu / $data_limit;
        } else {
            $tu = floor($tu / $data_limit) + 1;
        }
        if (isset($req->search)) {
            $user_list = $this->user_search_list(user_group: $req->ug, keyword: $req->search, ord: "DESC", limit: $page_limit, active: 1);
        } else {
            $user_list = $this->user_list(user_group: $req->ug, ord: "DESC", limit: $page_limit, active: 1);
        }
        $context = (object) array(
            'page' => 'users/list.php',
            'data' => (object) array(
                'req' => obj($req),
                'user_list' => $user_list,
                'total_user' => $tu,
                'current_page' => $cp,
                'is_active' => true
            )
        );
        $this->render_main($context);
    }

    // User search list
    public function user_search_list($user_group = 'user', $keyword = '', $ord = "DESC", $limit = 5, $active = 1)
    {
        $cntobj = new Model('pk_user');
        $search_arr['username'] = $keyword;
        $search_arr['email'] = $keyword;
        $search_arr['first_name'] = $keyword;
        $search_arr['last_name'] = $keyword;
        $search_arr['mobile'] = $keyword;
        return $cntobj->search(
            assoc_arr: $search_arr,
            ord: $ord,
            limit: $limit,
            whr_arr: array('user_group' => $user_group, 'is_active' => $active)
        );
    }
    // Trashed user list
    public function trash_list($req = null)
    {
        $req = obj($req);

        $current_page = 0;
        $data_limit = DB_ROW_LIMIT;
        $page_limit = "0,$data_limit";
        $cp = 0;
        if (isset($req->page) && intval($req->page)) {
            $cp = $req->page;
            $current_page = (abs($req->page) - 1) * $data_limit;
            $page_limit = "$current_page,$data_limit";
        }
        $total_user = $this->user_list(user_group: $req->ug, ord: "DESC", limit: 10000, active: 0);
        $tu = count($total_user);
        if ($tu %  $data_limit == 0) {
            $tu = $tu / $data_limit;
        } else {
            $tp = floor($tu / $data_limit) + 1;
        }
        if (isset($req->search)) {
            $user_list = $this->user_search_list($user_group = $req->ug, $keyword = $req->search, $ord = "DESC", $limit = $page_limit, $active = 0);
        } else {
            $user_list = $this->user_list(user_group: $req->ug, ord: "DESC", limit: $page_limit, active: 0);
        }
        $context = (object) array(
            'page' => 'users/list.php',
            'data' => (object) array(
                'req' => obj($req),
                'user_list' => $user_list,
                'total_user' => $tu,
                'current_page' => $cp,
                'is_active' => false
            )
        );
        $this->render_main($context);
    }
    // Edit page
    public function edit($req = null)
    {
        $req = obj($req);
        $context = (object) array(
            'page' => 'users/edit.php',
            'data' => (object) array(
                'req' => obj($req),
                'user_detail' => $this->user_detail(id: $req->id, user_group: $req->ug)
            )
        );
        $this->render_main($context);
    }
    // Save user by ajax call
    public function save($req = null)
    {
        $req = obj($req);
        $request = null;
        $data = null;
        $data = $_POST;


        $data['image'] = $_FILES['image']??null;
        $data['vhcl_doc'] = $_FILES['vhcl_doc']??null;
        $data['dl_doc'] = $_FILES['dl_doc']??null;
        $data['nid_doc'] = $_FILES['nid_doc']??null;

        $rules = [
            'email' => 'required|email',
            'username' => 'required|string|min:4',
            'image' => 'required|file',
            'first_name' => 'required|string',
            'password' => 'required|string'
        ];
        if ($req->ug == 'driver') {
            $rules_driver = [
                'dl_doc' => 'required|file',
                'nid_doc' => 'required|file',
                'vhcl_doc' => 'required|file',
                'dl_no' => 'required|string',
                'nid_no' => 'required|string',
                'vhcl_no' => 'required|string',
            ];
            $rules = array_merge($rules, $rules_driver);
        }
        $pass = validateData(data: $data, rules: $rules);
        // echo js_alert(msg_ssn("msg", true));
        // return;
        if (!$pass) {
            echo js_alert(msg_ssn("msg", true));
            exit;
        }

        $request = obj($data);
        $username_exists = (new Model('pk_user'))->exists(['username' => generate_clean_username($request->username)]);
        $email_exists = (new Model('pk_user'))->exists(['email' => $request->email]);
        if ($username_exists) {
            $_SESSION['msg'][] = 'Usernam not availble please try with another usernam';
            echo js_alert(msg_ssn("msg", true));
            exit;
        }
        if ($email_exists) {
            $_SESSION['msg'][] = 'Email is already exists';
            echo js_alert(msg_ssn("msg", true));
            exit;
        }
        if (isset($request->email)) {
            $arr = null;
            $arr['user_group'] = $req->ug;
            $arr['email'] = $request->email;
            $arr['username'] = generate_clean_username($request->username);
            $arr['first_name'] = sanitize_remove_tags($request->first_name);
            $arr['last_name'] = sanitize_remove_tags($request->last_name);
            $arr['nid_no'] = sanitize_remove_tags($request->nid_no);
            $arr['dl_no'] = sanitize_remove_tags($request->dl_no);
            $arr['vhcl_no'] = sanitize_remove_tags($request->vhcl_no);
            $arr['password'] = md5($request->password);
            if (isset($request->bio)) {
                $arr['bio'] = sanitize_remove_tags($request->bio);
            }
            $arr['created_at'] = date('Y-m-d H:i:s');
            $postid = (new Model('pk_user'))->store($arr);
            if (intval($postid)) {
                if (isset($request->image)) {
                    $ext = pathinfo($request->image['name'], PATHINFO_EXTENSION);
                    $imgname = str_replace(" ", "_", getUrlSafeString($request->username)) . uniqid("_") . "." . $ext;
                    $dir = MEDIA_ROOT . "images/profiles/" . $imgname;
                    $upload = move_uploaded_file($request->image['tmp_name'], $dir);
                    if ($upload) {
                        $filearr['image'] = $imgname;
                    }
                    
                }
                if (isset($request->nid_doc)) {
                    $ext = pathinfo($request->nid_doc['name'], PATHINFO_EXTENSION);
                    $docname = str_replace(" ", "_", getUrlSafeString($request->username)) . uniqid("_") . "." . $ext;
                    $dir = MEDIA_ROOT . "docs/" . $docname;
                    $upload = move_uploaded_file($request->nid_doc['tmp_name'], $dir);
                    if ($upload) {
                        $filearr['nid_doc'] = $imgname;
                    }
                    
                }
                if (isset($request->dl_doc)) {
                    $ext = pathinfo($request->dl_doc['name'], PATHINFO_EXTENSION);
                    $docname = str_replace(" ", "_", getUrlSafeString($request->username)) . uniqid("_") . "." . $ext;
                    $dir = MEDIA_ROOT . "docs/" . $docname;
                    $upload = move_uploaded_file($request->dl_doc['tmp_name'], $dir);
                    if ($upload) {
                        $filearr['dl_doc'] = $imgname;
                    }
                   
                }
                if (isset($request->vhcl_doc)) {
                    $ext = pathinfo($request->vhcl_doc['name'], PATHINFO_EXTENSION);
                    $docname = str_replace(" ", "_", getUrlSafeString($request->username)) . uniqid("_") . "." . $ext;
                    $dir = MEDIA_ROOT . "docs/" . $docname;
                    $upload = move_uploaded_file($request->vhcl_doc['tmp_name'], $dir);
                    if ($upload) {
                        $filearr['vhcl_doc'] = $imgname;
                    }
                    
                }
                if (count($filearr)) {
                    (new Model('pk_user'))->update($postid, $filearr);
                }
                echo js_alert('Account created');
                echo go_to(route('userList', ['ug' => $req->ug]));
                return true;
            } else {
                echo js_alert('Account not created');
                return false;
            }
        } else {
            echo js_alert('Missing required field, uaser not created');
            return false;
        }
    }
    // Save by ajax call
    public function update($req = null)
    {
        $req = obj($req);
        $user_exists = (new Model('pk_user'))->exists(['id' => $req->id, 'user_group' => $req->ug]);
        if ($user_exists == false) {
            $_SESSION['msg'][] = "Object not found";
            echo js_alert(msg_ssn("msg", true));
            exit;
        }
        $user = obj(getData(table: 'pk_user', id: $req->id));
        $request = null;
        $data = null;
        $data = $_POST;
        $data['id'] = $req->id;
        if (isset($_FILES['image'])) {
            $data['image'] = $_FILES['image'];
        } else {
            $data['image'] = false;
        }

        $data['image'] = $_FILES['image']??null;
        $data['vhcl_doc'] = $_FILES['vhcl_doc']??null;
        $data['dl_doc'] = $_FILES['dl_doc']??null;
        $data['nid_doc'] = $_FILES['nid_doc']??null;

        $rules = [
            'email' => 'required|email',
            'username' => 'required|string|min:4|max:16',
            'first_name' => 'required|string'
        ];
        if ($req->ug == 'driver') {
            $rules_driver = [
                'dl_no' => 'required|string',
                'nid_no' => 'required|string',
                'vhcl_no' => 'required|string',
            ];
            $rules = array_merge($rules, $rules_driver);
        }
        $pass = validateData(data: $data, rules: $rules);
        if (!$pass) {
            echo js_alert(msg_ssn("msg", true));
            exit;
        }
        $request = obj($data);
        $username_exists = (new Model('pk_user'))->exists(['username' => generate_clean_username($request->username)]);
        $email_exists = (new Model('pk_user'))->exists(['email' => $request->email]);
        if ($username_exists) {
            if ($user->username != $request->username) {
                $_SESSION['msg'][] = 'Usernam not availble, for change please try with another usernam';
                echo js_alert(msg_ssn("msg", true));
                exit;
            }
        }
        if ($email_exists) {

            if ($user->email != $request->email) {
                $_SESSION['msg'][] = 'Email not available for change';
                echo js_alert(msg_ssn("msg", true));
                exit;
            }
        }

        if (isset($request->email)) {
            $arr = null;
            // if ($request?->role=='superuser') {
            //     $request->role=='admin';
            // }
            if ($user->username != 'admin') {
                $arr['username'] = generate_clean_username($request->username);
                // $arr['role'] = sanitize_remove_tags($request->role);
                $arr['user_group'] = $req->ug;
            }
            $arr['email'] = $request->email;

            $arr['first_name'] = sanitize_remove_tags($request->first_name);
            $arr['last_name'] = sanitize_remove_tags($request->last_name);


            if (isset($request->password) && $request->password != "") {
                $arr['password'] = md5($request->password);
                $_SESSION['msg'][] = "Password updated";
            }
            if (isset($request->bio)) {
                $arr['bio'] = sanitize_remove_tags($request->bio);
            }
            $arr['updated_at'] = date('Y-m-d H:i:s');

            $arr['nid_no'] = sanitize_remove_tags($request->nid_no??null);
            $arr['dl_no'] = sanitize_remove_tags($request->dl_no??null);
            $arr['vhcl_no'] = sanitize_remove_tags($request->vhcl_no??null);



            $old = obj($user);
            if (isset($request->image) && $request->image['name'] != "" && $request->image['error'] == 0) {
                $ext = pathinfo($request->image['name'], PATHINFO_EXTENSION);
                $imgname = str_replace(" ", "_", getUrlSafeString($request->username)) . uniqid("_") . "." . $ext;
                $dir = MEDIA_ROOT . "images/profiles/" . $imgname;
                $upload = move_uploaded_file($request->image['tmp_name'], $dir);
                if ($upload) {
                    $arr['image'] = $imgname;
                    // $old = obj($user);
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
                    // $old = obj($user);
                    if ($old) {
                        if ($old->nid_doc != "") {
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
                    // $old = obj($user);
                    if ($old) {
                        if ($old->dl_doc != "") {
                            $olddir = MEDIA_ROOT . "docs/" . $old->dl_doc;
                            if (file_exists($olddir)) {
                                unlink($olddir);
                            }
                        }
                    }
                    $filearr['dl_doc'] = $docname;
                }
                
            }
            if (isset($request->vhcl_doc) && $request->vhcl_doc['name'] != "" && $request->vhcl_doc['error'] == 0) {
                $ext = pathinfo($request->vhcl_doc['name'], PATHINFO_EXTENSION);
                $docname = str_replace(" ", "_", getUrlSafeString($request->username)) . uniqid("_") . "." . $ext;
                $dir = MEDIA_ROOT . "docs/" . $docname;
                $upload = move_uploaded_file($request->vhcl_doc['tmp_name'], $dir);
                if ($upload) {
                    $arr['vhcl_doc'] = $docname;
                    // $old = obj($user);
                    if ($old) {
                        if ($old->vhcl_doc != "") {
                            $olddir = MEDIA_ROOT . "docs/" . $old->vhcl_doc;
                            if (file_exists($olddir)) {
                                unlink($olddir);
                            }
                        }
                    }
                    $filearr['vhcl_doc'] = $docname;
                }
                
            }


           
            try {
                (new Model('pk_user'))->update($request->id, $arr);
                $_SESSION['msg'][] = "Updated";
                echo js_alert(msg_ssn(return: true));
                echo go_to(route('userEdit', ['ug' => $req->ug, 'id' => $request->id]));
                exit;
            } catch (PDOException $e) {
                echo js_alert('User not updated');
                exit;
            }
        }
    }
    public function move_to_trash($req = null)
    {
        $req = obj($req);
        $user_exists = (new Model('pk_user'))->exists(['id' => $req->id, 'user_group' => $req->ug]);
        if ($user_exists == false) {
            $_SESSION['msg'][] = "Object not found";
            echo js_alert(msg_ssn("msg", true));
            echo go_to(route('userList', ['ug' => $req->ug]));
            exit;
        }
        // $user = obj(getData(table: 'pk_user', id: $req->id));
        $data = null;
        $data['id'] = $req->id;
        $rules = [
            'id' => 'required|integer'
        ];
        $pass = validateData(data: $data, rules: $rules);
        if (!$pass) {
            echo js_alert(msg_ssn("msg", true));
            echo go_to(route('userList', ['ug' => $req->ug]));
            exit;
        }
        try {
            (new Model('pk_user'))->update($req->id, array('is_active' => 0));
            // echo js_alert('User moved to trash');
            echo go_to(route('userList', ['ug' => $req->ug]));
            exit;
        } catch (PDOException $e) {
            echo js_alert('Account not moved to trash');
            exit;
        }
    }
    public function restore($req = null)
    {
        $req = obj($req);
        $user_exists = (new Model('pk_user'))->exists(['id' => $req->id, 'user_group' => $req->ug]);
        if ($user_exists == false) {
            $_SESSION['msg'][] = "Object not found";
            echo js_alert(msg_ssn("msg", true));
            echo go_to(route('userTrashList', ['ug' => $req->ug]));
            exit;
        }
        // $user = obj(getData(table: 'pk_user', id: $req->id));
        $data = null;
        $data['id'] = $req->id;
        $rules = [
            'id' => 'required|integer'
        ];
        $pass = validateData(data: $data, rules: $rules);
        if (!$pass) {
            echo js_alert(msg_ssn("msg", true));
            echo go_to(route('userTrashList', ['ug' => $req->ug]));
            exit;
        }
        try {
            (new Model('pk_user'))->update($req->id, array('is_active' => 1));
            echo js_alert('User restored');
            echo go_to(route('userTrashList', ['ug' => $req->ug]));
            exit;
        } catch (PDOException $e) {
            echo js_alert('User can not be restored');
            exit;
        }
    }
    public function delete_trash($req = null)
    {
        $req = obj($req);
        $user_exists = (new Model('pk_user'))->exists(['id' => $req->id, 'user_group' => $req->ug]);
        if ($user_exists == false) {
            $_SESSION['msg'][] = "Object not found";
            echo js_alert(msg_ssn("msg", true));
            echo go_to(route('userTrashList', ['ug' => $req->ug]));
            exit;
        }
        // $user = obj(getData(table: 'pk_user', id: $req->id));
        $data = null;
        $data['id'] = $req->id;
        $rules = [
            'id' => 'required|integer'
        ];
        $pass = validateData(data: $data, rules: $rules);
        if (!$pass) {
            echo js_alert(msg_ssn("msg", true));
            echo go_to(route('userTrashList', ['ug' => $req->ug]));
            exit;
        }
        try {
            $content_exists = (new Model('pk_user'))->exists(['id' => $req->id, 'is_active' => 0, 'user_group' => $req->ug]);
            if ($content_exists) {
                $user = obj(getData('pk_user', $req->id));
                if ($user->username == 'admin') {
                    echo js_alert('Supreme account cannot be deleted');
                    echo go_to(route('userTrashList', ['ug' => $req->ug]));
                    exit;
                }
                if ((new Model('pk_user'))->destroy($req->id)) {
                    echo js_alert('User deleted permanatly');
                    echo go_to(route('userTrashList', ['ug' => $req->ug]));
                    exit;
                }
            }
            echo js_alert('User does not exist');
            echo go_to(route('userTrashList', ['ug' => $req->ug]));
            exit;
        } catch (PDOException $e) {
            echo js_alert('User not deleted');
            exit;
        }
    }

    // User list
    public function user_list($user_group = "user", $ord = "DESC", $limit = 5, $active = 1)
    {
        $cntobj = new Model('pk_user');
        return $cntobj->filter_index(array('user_group' => $user_group, 'is_active' => $active), $ord, $limit);
    }
    // User detail
    public function user_detail($id, $user_group = 'user')
    {
        $cntobj = new Model('pk_user');
        $exists = $cntobj->exists(array('user_group' => $user_group, 'id' => $id));
        if ($exists) {
            return $cntobj->show($id);
        } else {
            return false;
        }
    }
    // render function
    public function render_main($context = null)
    {
        import("apps/admin/layouts/admin-main.php", $context);
    }
}
