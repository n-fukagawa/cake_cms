<?php
// src/Controller/ArticlesController.php

namespace App\Controller;

class ArticlesController extends AppController
{
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Paginator');
        $this->loadComponent('Flash'); // FlashComponent をインクルード
        $this->Auth->allow(['tags']);
    }
    // indexページを表示する
    public function index()
    {
        $this->loadComponent('Paginator');
        $articles = $this->Paginator->paginate($this->Articles->find());
        $this->set(compact('articles'));
    }
    // $slug を元にデータを取得しviewページを表示する
    public function view($slug = null)
    {
        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        $this->set(compact('article'));
    }
    public function add()
    {
        $article = $this->Articles->newEntity();
        // リクエストの HTTP メソッドが POST だった場合、Articles モデルを使用してデータを保存しようとします。
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());

            // 変更: セッションから user_id をセット
            $article->user_id = $this->Auth->user('id');

            // $articleをArticlesテーブルに保存できたら
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to add your article.'));
        }
        // タグのリストを取得
        // find('list') Tagsのデータを一覧で取得する（全件）
        $tags = $this->Articles->Tags->find('list');

        // ビューにデータを渡す
        $this->set('tags', $tags);
        $this->set('article', $article);
        $this->set('article', $article);
    }
    public function edit($slug)
    {
        // Articleテーブルからデータを１件取得
        // contain() 関連づけられたTagsも読み込む
        $article = $this->Articles->findBySlug($slug)->contain('Tags')->firstOrFail();
        // post or put ?
        if ($this->request->is(['post', 'put'])) {
            // pathEntity() 変数$argicleのデータを編集する
            $this->Articles->patchEntity($article, $this->request->getData(),[
                // user_id アクセス不可
                'accessibleFields' => ['user_id' => false]
            ]);
            // save() 編集した変数$articleをDBへ更新する
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to update your article.'));
        }
        // タグのリストを取得
        $tags = $this->Articles->Tags->find('list');

        // ビューコンテキストに tags をセット
        $this->set('tags', $tags);
    
        $this->set('article', $article);
        $this->set('article', $article);
    }
    public function delete($slug)
    {
        // post delete メソッドのみ受け入れる
        $this->request->allowMethod(['post', 'delete']);
    
        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        // $articleの削除
        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The {0} article has been deleted.', $article->title));
            return $this->redirect(['action' => 'index']);
        }
    }
    public function tags()
    {
        // 'pass' キーは CakePHP によって提供され、リクエストに渡された
        // 全ての URL パスセグメントを含みます。
        $tags = $this->request->getParam('pass');
    
        // ArticlesTable を使用してタグ付きの記事を検索します。
        $articles = $this->Articles->find('tagged', [
            'tags' => $tags
        ]);
    
        // 変数をビューテンプレートのコンテキストに渡します。
        $this->set([
            'articles' => $articles,
            'tags' => $tags
        ]);
    }
    public function isAuthorized($user)
    {
        $action = $this->request->getParam('action');
        // add および tags アクションは、常にログインしているユーザーに許可されます。
        if (in_array($action, ['add', 'tags'])) {
            return true;
        }
    
        // 他のすべてのアクションにはスラッグが必要です。
        $slug = $this->request->getParam('pass.0');
        if (!$slug) {
            return false;
        }
    
        // 記事が現在のユーザーに属していることを確認します。
        $article = $this->Articles->findBySlug($slug)->first();
    
        return $article->user_id === $user['id'];
    }
}

// viewアクション
// $slug にはユーザーからのリクエストによって値が入ってくる
//     /articles/view/first-post だと"first-post"が入ってくる
// firstOrFail()
//     １件目を取得するか失敗か(例外発生)
// $this->Articles->findBySlug($slug)
//     Articlesテーブルからslug列が変数$slugのデータを取得する
