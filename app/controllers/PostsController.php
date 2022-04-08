<?php
declare(strict_types=1);

 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model;


class PostsController extends ControllerBase
{
    /**
     * Index action
     */
    public function indexAction()
    {
        //
    }

    /**
     * Searches for posts
     */
    public function searchAction()
    {
        $numberPage = $this->request->getQuery('page', 'int', 1);
        $parameters = Criteria::fromInput($this->di, 'Posts', $_GET)->getParams();
        $parameters['order'] = "id";

        $paginator   = new Model(
            [
                'model'      => 'Posts',
                'parameters' => $parameters,
                'limit'      => 10,
                'page'       => $numberPage,
            ]
        );

        $paginate = $paginator->paginate();

        if (0 === $paginate->getTotalItems()) {
            $this->flash->notice("The search did not find any posts");

            $this->dispatcher->forward([
                "controller" => "posts",
                "action" => "index"
            ]);

            return;
        }

        $this->view->page = $paginate;
    }

    /**
     * Displays the creation form
     */
    public function newAction()
    {
        //
    }

    /**
     * Edits a post
     *
     * @param string $id
     */
    public function editAction($id)
    {
        if (!$this->request->isPost()) {
            $post = Posts::findFirstByid($id);
            if (!$post) {
                $this->flash->error("post was not found");

                $this->dispatcher->forward([
                    'controller' => "posts",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->id = $post->id;

            $this->tag->setDefault("id", $post->id);
            $this->tag->setDefault("title", $post->title);
            $this->tag->setDefault("body", $post->body);
            $this->tag->setDefault("excerpt", $post->excerpt);
            $this->tag->setDefault("created_at", $post->created_at);
            $this->tag->setDefault("updated_at", $post->updated_at);
            
        }
    }

    /**
     * Creates a new post
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'index'
            ]);

            return;
        }

        $post = new Posts();
        $post->title = $this->request->getPost("title");
        $post->body = $this->request->getPost("body");
        $post->excerpt = $this->request->getPost("excerpt");
        $post->createdAt = $this->request->getPost("created_at");
        $post->updatedAt = $this->request->getPost("updated_at");
        

        if (!$post->save()) {
            foreach ($post->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("post was created successfully");

        $this->dispatcher->forward([
            'controller' => "posts",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a post edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'index'
            ]);

            return;
        }

        $id = $this->request->getPost("id");
        $post = Posts::findFirstByid($id);

        if (!$post) {
            $this->flash->error("post does not exist " . $id);

            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'index'
            ]);

            return;
        }

        $post->title = $this->request->getPost("title");
        $post->body = $this->request->getPost("body");
        $post->excerpt = $this->request->getPost("excerpt");
        $post->createdAt = $this->request->getPost("created_at");
        $post->updatedAt = $this->request->getPost("updated_at");
        

        if (!$post->save()) {

            foreach ($post->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'edit',
                'params' => [$post->id]
            ]);

            return;
        }

        $this->flash->success("post was updated successfully");

        $this->dispatcher->forward([
            'controller' => "posts",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a post
     *
     * @param string $id
     */
    public function deleteAction($id)
    {
        $post = Posts::findFirstByid($id);
        if (!$post) {
            $this->flash->error("post was not found");

            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'index'
            ]);

            return;
        }

        if (!$post->delete()) {

            foreach ($post->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "posts",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("post was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "posts",
            'action' => "index"
        ]);
    }
}
