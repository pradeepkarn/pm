<?php

use League\Csv\Reader;

class Product_category_admin_ctrl
{
    // Cretae page
    public function create($req = null)
    {
        $context = (object) array(
            'page' => 'product_category/create.php',
            'data' => (object) array(
                'req' => obj($req),
                'other_data' => 'other_data'
            )
        );
        $this->render_main($context);
    }
    // List page
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
        $total_post = $this->cat_list(ord: "DESC", limit: 10000, active: 1);
        $tp = count($total_post);
        if ($tp %  $data_limit == 0) {
            $tp = $tp / $data_limit;
        } else {
            $tp = floor($tp / $data_limit) + 1;
        }
        if (isset($req->search)) {
            $post_list = $this->cat_search_list($keyword = $req->search, $ord = "DESC", $limit = $page_limit, $active = 1);
        } else {
            $post_list = $this->cat_list(ord: "DESC", limit: $page_limit, active: 1);
        }
        $context = (object) array(
            'page' => 'product_category/list.php',
            'data' => (object) array(
                'req' => obj($req),
                'post_list' => $post_list,
                'total_post' => $tp,
                'current_page' => $cp,
                'is_active' => true
            )
        );
        $this->render_main($context);
    }
    // Trashed post list
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
        $total_post = $this->cat_list(ord: "DESC", limit: 10000, active: 0);
        $tp = count($total_post);
        if ($tp %  $data_limit == 0) {
            $tp = $tp / $data_limit;
        } else {
            $tp = floor($tp / $data_limit) + 1;
        }
        if (isset($req->search)) {
            $post_list = $this->cat_search_list($keyword = $req->search, $ord = "DESC", $limit = $page_limit, $active = 0);
        } else {
            $post_list = $this->cat_list(ord: "DESC", limit: $page_limit, active: 0);
        }
        $context = (object) array(
            'page' => 'product_category/list.php',
            'data' => (object) array(
                'req' => obj($req),
                'post_list' => $post_list,
                'total_post' => $tp,
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
            'page' => 'product_category/edit.php',
            'data' => (object) array(
                'req' => obj($req),
                'cat_detail' => $this->cat_detail($req->id)
            )
        );
        $this->render_main($context);
    }
    //  function upload_bulk_game_with_cat(object $db, array $data = [])
    // {
    //     $data['csvfile'] = $_FILES['csvfile'] ?? null;
    //     if ($data['csvfile']==null) {
    //         return false;
    //     }
    //     $rules = [
    //         'csvfile' => 'required|file',
    //         'game_id' => 'required|numeric',
    //     ];

    //     $pass = validateData(data: $data, rules: $rules);
    //     if (!$pass) {
    //         echo js_alert(msg_ssn("msg", true));
    //         exit;
    //     }
    //     $req = obj($data);
    //     if ($req->csvfile['name'] != "" && $req->csvfile['error'] == 0) {
    //         $ext = pathinfo($req->csvfile['name'], PATHINFO_EXTENSION);
    //         if ($ext != 'csv') {
    //             msg_set('Invalid file format, please provide .csv');
    //             echo js_alert(msg_ssn("msg", true));
    //             exit;
    //         } else {
    //             $csvFilePath = $req->csvfile['tmp_name'];
    //             $csv = Reader::createFromPath($csvFilePath, 'r');
    //             $csv->setHeaderOffset(0); // Assumes the first row contains headers
    //             $total = iterator_count($csv->getRecords());
    //             foreach ($csv->getRecords() as $key => $record) {
    //                 set_time_limit(60);
    //                 $rc = obj($record);
    //                 $exists = $db->showOne("select link from content where content_group='game' and link='{$rc->url}'");
    //                 $db->tableName = 'content';
    //                 $db->insertData['title'] = $req->title;
    //                 // $db->insertData['content'] = $req->details;
    //                 // $db->insertData['price'] = $rc->price;
    //                 $db->insertData['link'] = $rc->url;
    //                 $db->insertData['parent_id'] = $req->game_id;
    //                 $db->insertData['content_group'] = 'game';
    //                 $db->insertData['slug'] = generate_slug(uniqid($req->title));
    //                 // $db->insertData['opens_at'] = $rc->opens_at;
    //                 // $db->insertData['closes_at'] = $rc->closes_at;
    //                 $db->insertData['created_by'] = USER['id'];
    //                 try {
    //                     if (!$exists) {
    //                         $db->create();
    //                     }
    //                 } catch (PDOException $th) {
    //                     throw $th;
    //                 };
    //                 echo server_progress($key,$total)."<br>";
    //             }
    //         }
    //     }
    // }
    // Save by ajax call
    public function save($req = null)
    {
        $request = null;
        $data = null;
        $data = $_POST;
        $data['banner'] = $_FILES['banner'];
        $rules = [
            'title' => 'required|string'
        ];
        $pass = validateData(data: $data, rules: $rules);
        if (!$pass) {
            echo js_alert(msg_ssn("msg", true));
            exit;
        }
        $request = obj($data);
        if (isset($request->title)) {
            $arr = null;
            $arr['content_group'] = "product_category";
            $arr['title'] = $request->title;
            $arr['slug'] = generate_slug($request->title);
            $arr['content'] = $request->content??null;
            $postid = (new Model('content'))->store($arr);
            if (intval($postid) && isset($request->banner)) {
                if ($request->banner['name'] != '') {
                    $ext = pathinfo($request->banner['name'], PATHINFO_EXTENSION);
                    $imgname = str_replace(" ", "_", getUrlSafeString($request->title)) . uniqid("_") . "." . $ext;
                    $dir = MEDIA_ROOT . "images/pages/" . $imgname;
                    $upload = move_uploaded_file($request->banner['tmp_name'], $dir);
                    if ($upload) {
                        (new Model('content'))->update($postid, array('banner' => $imgname));
                    }
                }
                echo js_alert('Content created');
                echo go_to(route('productCatList'));
            } else {
                echo js_alert('Content not created');
                return false;
            }
        }
    }
    // Save by ajax call
    public function update($req = null)
    {
        $req = obj($req);
        $content = obj(getData(table: 'content', id: $req->id));
        if ($content == false) {
            $_SESSION['msg'][] = "Object not found";
            echo js_alert(msg_ssn("msg", true));
            exit;
        }
        $request = null;
        $data = null;
        $data = $_POST;
        $data['id'] = $req->id;
        $data['banner'] = $_FILES['banner'];
        $rules = [
            'id' => 'required|integer',
            'title' => 'required|string'
        ];
        $pass = validateData(data: $data, rules: $rules);
        if (!$pass) {
            echo js_alert(msg_ssn("msg", true));
            exit;
        }
        $request = obj($data);
        if (isset($request->title)) {
            $arr = null;
            $arr['content_group'] = "product_category";
            $arr['title'] = $request->title;
            $arr['slug'] = generate_slug($request->title);
            $arr['content'] = $request->content??$content->content;
            if ($request->banner['name'] != "" && $request->banner['error'] == 0) {
                $ext = pathinfo($request->banner['name'], PATHINFO_EXTENSION);
                $imgname = str_replace(" ", "_", getUrlSafeString($request->title)) . uniqid("_") . "." . $ext;
                $dir = MEDIA_ROOT . "images/pages/" . $imgname;
                $upload = move_uploaded_file($request->banner['tmp_name'], $dir);
                if ($upload) {
                    $arr['banner'] = $imgname;
                    $old = obj($content);
                    if ($old) {
                        if ($old->banner != "") {
                            $olddir = MEDIA_ROOT . "images/pages/" . $old->banner;
                            if (file_exists($olddir)) {
                                unlink($olddir);
                            }
                        }
                    }
                }
            }
            try {
                (new Model('content'))->update($request->id, $arr);

                echo js_alert('Content updated');
                //  echo go_to(route('productCatList', ['id' => $request->id]));
                echo RELOAD;
                exit;
            } catch (PDOException $e) {
                echo js_alert('Content not updated');
                exit;
            }
        }
    }
    public function move_to_trash($req = null)
    {
        $req = obj($req);
        $content = obj(getData(table: 'content', id: $req->id));
        if ($content == false) {
            $_SESSION['msg'][] = "Object not found";
            echo js_alert(msg_ssn("msg", true));
            exit;
        }
        $data = null;
        $data['id'] = $req->id;
        $rules = [
            'id' => 'required|integer'
        ];
        $pass = validateData(data: $data, rules: $rules);
        if (!$pass) {
            echo js_alert(msg_ssn("msg", true));
            exit;
        }
        try {
            (new Model('content'))->update($req->id, array('is_active' => 0));
            echo js_alert('Content moved to trash');
            echo go_to(route('productCatList'));
            exit;
        } catch (PDOException $e) {
            echo js_alert('Content not moved to trash');
            exit;
        }
    }
    public function restore($req = null)
    {
        $req = obj($req);
        $content = obj(getData(table: 'content', id: $req->id));
        if ($content == false) {
            $_SESSION['msg'][] = "Object not found";
            echo js_alert(msg_ssn("msg", true));
            exit;
        }
        $data = null;
        $data['id'] = $req->id;
        $rules = [
            'id' => 'required|integer'
        ];
        $pass = validateData(data: $data, rules: $rules);
        if (!$pass) {
            echo js_alert(msg_ssn("msg", true));
            exit;
        }
        try {
            (new Model('content'))->update($req->id, array('is_active' => 1));
            echo js_alert('Content moved to active list');
            echo go_to(route('productCatTrashList'));
            exit;
        } catch (PDOException $e) {
            echo js_alert('Content not moved to active list');
            exit;
        }
    }
    public function delete_trash($req = null)
    {
        $req = obj($req);
        $content = obj(getData(table: 'content', id: $req->id));
        if ($content == false) {
            $_SESSION['msg'][] = "Object not found";
            echo js_alert(msg_ssn("msg", true));
            exit;
        }
        $data = null;
        $data['id'] = $req->id;
        $rules = [
            'id' => 'required|integer'
        ];
        $pass = validateData(data: $data, rules: $rules);
        if (!$pass) {
            echo js_alert(msg_ssn("msg", true));
            exit;
        }
        try {
            $content_exists = (new Model('content'))->exists(['id' => $req->id, 'is_active' => 0]);
            if ($content_exists) {
                if ((new Model('content'))->destroy($req->id)) {
                    echo js_alert('Content deleted permanatly');
                    echo go_to(route('productCatTrashList'));
                    exit;
                }
            }
            echo js_alert('Content does not exixt');
            echo go_to(route('productCatTrashList'));
            exit;
        } catch (PDOException $e) {
            echo js_alert('Content not deleted');
            exit;
        }
    }
    // render function
    public function render_main($context = null)
    {
        import("apps/admin/layouts/admin-main.php", $context);
    }
    // Post list
    public function cat_list($ord = "DESC", $limit = 5, $active = 1)
    {
        $cntobj = new Model('content');
        return $cntobj->filter_index(array('content_group' => 'product_category', 'is_active' => $active), $ord, $limit);
    }
    public function cat_search_list($keyword, $ord = "DESC", $limit = 5, $active = 1)
    {
        $cntobj = new Model('content');
        $search_arr['id'] = $keyword;
        $search_arr['title'] = $keyword;
        // $search_arr['content'] = $keyword;
        $search_arr['author'] = $keyword;
        // $search_arr['created_at'] = $keyword;
        // $search_arr['updated_at'] = $keyword;
        return $cntobj->search(
            assoc_arr: $search_arr,
            ord: $ord,
            limit: $limit,
            whr_arr: array('content_group' => 'product_category', 'is_active' => $active)
        );
    }
    public function cat_detail($id)
    {
        $cntobj = new Model('content');
        return $cntobj->show($id);
    }
}
