<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;
use Cake\Routing\Router;
use Cake\Mailer\Email;
use Cake\Auth\DefaultPasswordHasher;
//use \CROSCON\CommissionJunction\Client;      



/**
 * Products Controller
 *
 * @property \App\Model\Table\ProductsTable $Products
 *
 * @method \App\Model\Entity\Product[] paginate($object = null, array $settings = [])
 */
class ProductsController extends AppController
{

    
    public function initialize()
    { 
        parent::initialize();
        $this->loadComponent('Cart');    
    }
    
    
        public function beforeFilter(Event $event) {

        parent::beforeFilter($event);



        $this->Auth->allow(['add', 'searchjson', 'search','view','index','addtocart','productbycat','promoteproduct']);        

        $this->authcontent(); 
    } 
    
    
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {  
        $this->paginate = [
            'contain' => ['Categories', 'Stores']
        ];
        $products = $this->paginate($this->Products);

        $this->set(compact('products'));
        $this->set('_serialize', ['products']);
    }

    
      public function productbycat($slug = NULL)
    {    
        $this->loadModel('Categories');
        $cat  = $this->Categories->find('all',array('conditions'=>array('Categories.slug'=>$slug)));
        $cat = $cat->first();
        $this->paginate = [
            'contain' => ['Categories', 'Stores'],
            'conditions'=>['Products.cat_id'=>$cat['id']]    
        ];
        $products = $this->paginate($this->Products);

        $this->set(compact('products','cat')); 
        $this->set('_serialize', ['products']);
    }
    
    
     public function promoteproduct()
    {    
        
    }
    
    
    
    /**
     * View method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($slug = null)
    {
    
        $product = $this->Products->find('all', array('contain'=>array('Users'),
                'conditions' => ['Products.slug'=>$slug] 
            ));
        $product = $product->first(); 
        $this->set('product', $product);
        $this->set('_serialize', ['product']);
    }

     public function searchjson() {
        $term = null;
        if(!empty($this->request->query['term'])) {
            $term = $this->request->query['term'];
            $terms = explode(' ', trim($term));
            $terms = array_diff($terms, array(''));
            $conditions = array(
                // 'Brand.active' => 1,
                'Products.status' => 1
            );
            foreach($terms as $term) {
                $conditions[] = array('Products.name LIKE' => '%' . $term . '%');
            }
            $products = $this->Products->find('all', array(
                'recursive' => -1,
                'contain' => array(
                     'Stores'
                ),
                'fields' => array(
                    'Products.id',
                    'Products.name',
                    'Products.image'
                ),
                'conditions' => $conditions,
                'limit' => 20,
            ));
        }
        
         $products = $products->all(); 
          $products = $products->toArray();
        
        echo json_encode($products);
        exit;

    }
    
    
    public function search() { 
        $search = null;
        if(!empty($this->request->query['search']) || !empty($this->request->data['name'])) {
            $search = empty($this->request->query['search']) ? $this->request->data['name'] : $this->request->query['search'];
            $search = preg_replace('/[^a-zA-Z0-9 ]/', '', $search);
            $terms = explode(' ', trim($search));
            $terms = array_diff($terms, array(''));
            $conditions = array(
                'Products.status' => 1
            );
            foreach($terms as $term) {
                $terms1[] = preg_replace('/[^a-zA-Z0-9]/', '', $term);
                $conditions[] = array('Products.name LIKE' => '%' . $term . '%');
            }
            
            
            $products = $this->Products->find('all', array(
                'contain' => array(
                    'Stores'
                ),
                'conditions' => $conditions,
                'limit' => 200,
            ));
            
            
             $products = $products->all(); 
             $products = $products->toArray();

            if(count($products) == 1) {
                return $this->redirect(array('controller' => 'products', 'action' => 'view/'.$products[0]['id']));
            }
            
         
            $terms1 = array_diff($terms1, array(''));
            $this->set(compact('products', 'terms1'));
        }
        $this->set(compact('search'));

        if ($this->request->is('ajax')) {
            $this->layout = false;
            $this->set('ajax', 1);
        } else {
            $this->set('ajax', 0);
        }

        $this->set('title_for_layout', 'Search');

        $description = 'Search';
        $this->set(compact('description'));

        $keywords = 'search';
        $this->set(compact('keywords'));
    }
    
    
    
    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $product = $this->Products->newEntity();
        if ($this->request->is('post')) {
            $product = $this->Products->patchEntity($product, $this->request->getData());
            if ($this->Products->save($product)) {
                $this->Flash->success(__('The product has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }
        $cats = $this->Products->Cats->find('list', ['limit' => 200]);
        $stores = $this->Products->Stores->find('list', ['limit' => 200]);
        $this->set(compact('product', 'cats', 'stores'));
        $this->set('_serialize', ['product']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $product = $this->Products->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $product = $this->Products->patchEntity($product, $this->request->getData());
            if ($this->Products->save($product)) {
                $this->Flash->success(__('The product has been saved.'));  

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }
        $cats = $this->Products->Cats->find('list', ['limit' => 200]);
        $stores = $this->Products->Stores->find('list', ['limit' => 200]);
        $this->set(compact('product', 'cats', 'stores'));
        $this->set('_serialize', ['product']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $product = $this->Products->get($id);
        if ($this->Products->delete($product)) {
            $this->Flash->success(__('The product has been deleted.'));
        } else {
            $this->Flash->error(__('The product could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    
    /************************Add to Cart module********************************/
    
      public function addtocart() {
        $this->loadModel('Carts');
        if ($this->request->is('post')) {
             $uid = $this->Auth->user('id');
             if(!empty($uid)){ 
               $uid = $uid;  
             }else{
                 $uid = 0 ;
             }
            $id = $this->request->data['id'];  

            $quantity = isset($this->request->data['quantity']) ? $this->request->data['quantity'] : null;

            $productmodId = isset($this->request->data['mods']) ? $this->request->data['mods'] : null;
            $exits = $this->Carts->find('all',array('conditions'=>array('AND'=>array('Carts.product_id'=>$id,'Carts.sessionid'=>$this->request->session()->id()))));
            $exits = $exits->first(); 
            if(!empty($exits)){
              $this->Flash->success(__('This Product already added in your shopping cart.'));   
             // $product = true; 
            }else{
            $product = $this->Cart->add($id, $quantity, $productmodId,$uid); 
                if(!empty($product)) { 
                    $this->Flash->success(__($product['name'] . ' was added to your shopping cart.'));
                } else {
                     $this->Flash->error(__('Unable to add this product to your shopping cart.'));

                } 
            
            }
        }  
        
        $this->redirect($this->referer());
    }
    
    
}
