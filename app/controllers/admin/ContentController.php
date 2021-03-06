<?php
namespace admin;

use View;
use Content;
use Category;
use Video;
use Input;
use Redirect;
use Exception;
use App;
use Auth;
use Session;
use Request;
use Response;
use User;
use Sanitizer;
use Carbon\Carbon;
use ContentForum;

class ContentController extends \BaseController {

    /**
     * Cross Site Request Forgery protection
     */
    public function __construct()
    {
        $this->beforeFilter('csrf', array('on' => 'post'));
        $this->data['active'] = 'content';
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if (Session::get('is_admin')) {
            $this->data['contents'] = Content::select('id', 'title', 'introtext', 'state')
                ->orderBy('id', 'desc')->paginate(20);
        } else {
            $this->data['contents'] = Content::select('id', 'title', 'introtext', 'state')
                ->orderBy('id', 'desc')->where('created_by', Auth::user()->id_member)->paginate(20);
        }
        return View::make('admin.content.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->data['categories'] = Category::intendCategories(Category::getCategoriesWithoutRoot());
        return View::make('admin.content.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $sanitize = new Sanitizer(Input::except('introtext', 'fullcontent'));
        Input::merge($sanitize->get());
        $sanitize = new Sanitizer(Input::only('introtext', 'fullcontent'), 'trim|htmlspecialchars');
        Input::merge($sanitize->get());

        $validation = new Content();
        if ($validation->validate(Input::all())) {

            $data = Input::except('slug');
            $data['created_by'] = Auth::user()->id_member;

            if (Session::get('is_admin')) {
                $data['created_by_alias'] = Input::get('created_by_alias');
            } else {
                $data['created_by_alias'] = Auth::user()->member_name;
            }

            $validation->fill($data);
            $validation->save();

            return Redirect::route('admin.content.user.edit', array('content' => $validation->id))
                ->with('global_success', 'Успешно добавяне на урок.');
        }

        return Redirect::back()->withInput()->withErrors($validation->getErrors());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        try {
            $this->data['content'] = Content::findOrFail($id);  
        } catch (Exception $e) {
            App::abort(404);
        }

        $this->data['videos'] = Video::where('content_id', $id)->get();

        $this->data['categories'] = Category::intendCategories(Category::getCategories());
        $this->data['route'] = 'edit';
        $this->data['content']['fullcontent'] = htmlspecialchars_decode($this->data['content']['fullcontent']);
        $this->data['content']['id_msg'] = $this->data['content']->getForumId($this->data['content']['id']);
        return View::make('admin.content.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        try {
            $content = Content::findOrFail($id);    
        } catch (Exception $e) {
            App::abort(404);
        }

        $sanitize = new Sanitizer(Input::except('introtext', 'fullcontent'));
        Input::merge($sanitize->get());
        $sanitize = new Sanitizer(Input::only('introtext', 'fullcontent'), 'trim|htmlspecialchars');
        Input::merge($sanitize->get());
        
        if ($content->validate(Input::all())) {
            $data = Input::all();
            $data['slug'] = Category::slug(Input::get('slug'));
            $content->update($data);
            return Redirect::route('admin.content.' . (Session::get('is_admin') === false ? 'user.' : null) . 'index')
               ->with('global_success', 'Данните бяха променени успешно.');
        }
        return Redirect::route('admin.content.edit', $id)->withErrors($content->getErrors());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        // Is the if statement really necessary?
        try {
            $content = Content::findOrFail($id);
        } catch (Exception $e) {
            App::abort(404);
        }

        if ($content->count()) {
            if (!Session::get('is_admin')) {
                if ($content->created_by !== Auth::user()->id_member) {
                    return Redirect::route('admin.content.user.index')
                        ->with('global_error', 'Можете да триете само собствените си уроци!');
                }
            }

            $videos = Video::where('content_id', $id)->get();
            if ($videos->count()) {
                foreach ($videos as $video) {
                    Video::deleteVideoFile($video->name);
                    $video->delete();
                }
            }
            $videos = Video::where('content_id', $id)->get();

            $content->delete();
            return Redirect::route('admin.content.user.index')->with('global_success', 'Съдържанието изтрито.');
        }
        return Redirect::route('admin.content.index')->with('global_error', 'Грешка при опит за изтриване!');
    }
    // END OF RESOURCEFUL CONTROLLERS

    /**
    *   Chucnk upload with Flow.js
    *   @link https://github.com/flowjs/flow.js/blob/master/samples/Backend%20on%20PHP.md
    */
    public function videoUpload($content)
    {
        // Limit uploaded content - one file on every 6 hours
        $count = Video::select('created_at')
            ->whereBetween('created_at', array(Carbon::now()->startOfDay(), Carbon::now()->endOfDay()))
            ->where('user_id', Auth::user()->id_member)
            ->orderBy('created_at', 'desc')
            ->count();
        if ($count >= 10 && Session::get('is_admin') != true) {
            throw new Exception("Вече достигнахте дневния си лимит за качване на видео.", 1);
        }

        $temp_dir = base_path() . '/upload/tmp/' . Input::get('flowIdentifier');

        if (Request::method() == 'GET') {
            $chunk_file = $temp_dir . '/' . Input::get('flowFilename') . '.part' . Input::get('flowChunkNumber');
            if (file_exists($chunk_file)) {
                $response = Response::make('HTTP/1.1', '200 Ok');
                $response->header('Content-Type', 'text/html');
                return $response;
            } else {
                $response = Response::make('HTTP/1.1', '404 Not Found');
                $response->header('Content-Type', 'text/html');
                return $response;
            }
        }
        $upload = new Video();
        $upload->handleFiles($temp_dir, (int)$content);
    }

    public function search()
    {
        $sanitize = new Sanitizer(Input::only('find'));
        Input::merge($sanitize->get());
        $find = Input::get('find');

        $created_by = Session::get('is_admin') === false ? Auth::user()->id_member : false;

        $this->data['contents'] = Content::search($find, array('created_by' => $created_by));
        $this->data['contents']->appends(array('find' => $find))->links();

        return View::make('admin.content.index', $this->data);
    }

    public function content_forum($id)
    {
        try {
            $content = Content::findOrFail($id);
        } catch (Exception $e) {
            App::abort(404);
        }

        // Update existing or insert a new record?
        $validation = ContentForum::where('content_id', $id)->first();
        if ($validation === null) {
            $validation = new ContentForum();
        }

        if ($validation->validate(Input::all())) {
            $validation->id_msg = Input::get('id_msg');
            $validation->content_id = $content->id;
            try {
                $validation->save();                
            } catch (Exception $e) {
                return Redirect::back()->with('global_error', 'Грешка: ' . $e->getMessage());
            }
            return Redirect::back()->with('global_success', 'Темата закачена успешно!');
        }
        return Redirect::back()->with('global_error', 'Грешка, моля опитайте отново!');
    }

}
