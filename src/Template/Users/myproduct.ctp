<div class="chk_section">
    <div class="container">
	<div class="alert-messge">
     <?= $this->Flash->render() ?>  
	 </div>
        <div class="row">
            <div class="my_thm">
                <a class="btn btn-success scss_grn" href="<?php echo $this->request->webroot."products/addsellproduct"; ?>">Add Product</a>
                <div class="my_hder">
                    <h3>My Products</h3>   
                </div>

                <!-----pro-table-------->               
                <div class="table_prodct">
				
                    <?php if(!empty($userdata['products'])){ ?>
					
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                            <tr class="clr_chmg">
                                <th scope="col">Title</th>
                                <th scope="col">QTY</th>
                                <th scope="col">Price</th>
                                <th scope="col" class="actions"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($userdata)){
                                
                             foreach($userdata['products'] as $product){      
                            ?>
                            <tr class="<?php if($product['bonus_disable_admin']==1){ echo "disble"; } ?>"> 
                                
                                <td data-label="Title" class="ttl_pnh">
                                    <div class="mypr_pic">
                                        <?php if($product['image']){ ?>  
                                        <img src="<?php echo $this->request->webroot."images/products/".$product['image']; ?>">
                                         <?php }else{ ?> 
                                        <img src="<?php echo $this->request->webroot."images/products/no-image.jpg"; ?>">
                                         <?php } ?> 
                                    </div>
                                    <div class="mypr_txt">
                                        <?php  
                                        $string = strip_tags($product['description']);
                                        if (strlen($string) > 30) {
                                            $stringCut = substr($string, 0, 30);
                                            $string = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
                                        }
                                        ?>
                                        <h4><?php if(isset($product['name'])){ echo $product['name']; } ?></h4>
                                        <p><?php if(isset($string)){ echo $string; } ?></p> 
                                    </div>



                                </td>  
                                <td data-label="QTY"><?php if($product['bonus_disable_admin']==1){ echo '<div class="disble-txttable">Product disabled by Admin </div>'; } ?> <?php if(isset($product['quantity'])){ echo $product['quantity']; } ?></td>
                                <td data-label="Price">$<?php if(isset($product['price'])){ echo $product['price']; } ?></td>
                                <td class="actions">
                                    <?= $this->Html->link(
                                         'View<span class="sr-only">' . __('View') . '</span>',
                                         ['controller'=>'products','action' => 'view', $product->slug],
                                         ['escape' => false, 'title' => __('View'), 'class' => 'btn btn-info btn-xs']
                                     ) ?>  
                                     <?= $this->Html->link(
                                         'Edit<span class="sr-only">' . __('Edit') . '</span>',
                                         ['controller'=>'products','action' => 'edit', $product->id],
                                         ['escape' => false, 'title' => __('Edit'), 'class' => 'btn btn-success btn-xs']
                                     ) ?>


                                     <?= $this->Form->postLink(__('Delete'), ['controller'=>'products','action' => 'delete', $product->id], ['confirm' => __('Are you sure you want to delete # {0}?', $product->id),'class' => 'btn btn-danger btn-xs']) ?>
                                 </td>
                            </tr>
                            <?php } } ?>      
                        </tbody>  
                    </table> 
					
                    <?php  }else{ echo '<div class="col-sm-12"><div class="blankimg"><img src="'.$this->request->webroot.'/img/no_product_5.png" class="img-responsive"></div></div>'; }  ?>  
					
                </div>
            </div>
        </div>
    </div>
</div>