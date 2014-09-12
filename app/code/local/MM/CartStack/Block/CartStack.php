<?php
/**
 * CartStack Block
 *
 * @category   MM
 * @package    MM_CartStack
 * @author     Teodora
 */

class MM_CartStack_Block_CartStack extends Mage_Core_Block_Template{
	
	public $module;
	public $controller;
	public $action;
	
	public function CartStackEnabled(){
		
		/**
		Detect if the plugin is enabled from Magento admin panel
		*/
		
		$CartStackEnabled = Mage::getStoreConfig('google/cartstack/cartstackenabled');
		return $CartStackEnabled;
		
	}
	
	public function CartStackID(){
		
		/**
		Get CartStack ID value entered in Magento admin panel
		*/
		
		$CartStackID = Mage::getStoreConfig('google/cartstack/cartstack_id');
		return $CartStackID;
		
	}
	
	public function PageType(){
		
		/**
		Detect page type
		*/
		
		$this->module = $this->getRequest()->getModuleName();
		$this->controller = $this->getRequest()->getControllerName();
		$this->action = $this->getRequest()->getActionName();
		
	}

	public function CartStackMethod(){
		
		/**
		Define CartStack method: tracking, capture or confirmation
		*/
		
		$this->PageType();
		
		if($this->module == 'contacts' && $this->controller == 'index'){
			$cartStackMethod = 'capture';
		} elseif($this->module == 'customer' && $this->controller == 'account'){
			$cartStackMethod = 'capture';
		} elseif($this->module == 'checkout' && $this->controller == 'onepage' && $this->action == 'success'){ 
			$cartStackMethod = 'confirmation';
		} elseif($this->module == 'checkout' && $this->controller == 'onepage' || $this->module == 'checkout' && $this->controller == 'cart'){
			$cartStackMethod = 'tracking';
		}
				
		return $cartStackMethod;
		
	}
		
	public function CartStack(){
		
		/**
		Main
		*/
		
		$html = '';
		$html = $this->CartStackBaseCode();
		
		$this->PageType();

		if($this->module == 'checkout' && $this->controller == 'onepage' && $this->action == 'success'){ 
			$pageType = 'success';
		}
		
		if ($pageType === cart) $html .= $this->CartStackCartContents();
		
		if($this->CartStackEnabled()){	
			return $html;
		}
		
	}
	
	private function CartStackBaseCode(){
		
		/**
		CartStack base code
		*/
		
		$CartStackBaseCode = '';
		$CartStackBaseCode = '<script src="https://api.cartstack.com/js/cs.js" type="text/javascript"></script>'."\n";
		$CartStackBaseCode .= '<script language="javascript">
			var _cartstack = _cartstack || [];
			_cartstack.push(["setSiteID", "'.$this->CartStackID().'"]);
			_cartstack.push(["setAPI", "'.$this->CartStackMethod().'"]);
			</script>';
			
		return $CartStackBaseCode;
		
	}
		
	private function CartStackCartContents(){
		
		/**
		CartStack code for cart
		*/
		
		$CartStackCartContentsCode = '';
		
		$cartContent = Mage::getModel('checkout/session')->getQuote();
		$cartData= $cartContent->getData();
		$cartTotal=$cartData['grand_total']; 
				
		$cartItems = $cartContent->getAllVisibleItems();
        foreach ($cartItems as $item){
		$product = Mage::getModel('catalog/product')->load($item->getProductId());
            $productId = $item->getProductId();
			$productQty = $item->getQty();
			$productName = $item->getName();
			$productDescription = $product->getShortDescription();
			$productURL = $product->getUrlInStore();
			$productImageURL = $product->getImageUrl();
			$productPrice = $item->getPrice();
			
			$CartStackCartContentsCode .= '<script>
				_cartstack.push(["setCartItem", {
				"quantity":"'.$productQty.'",
				"productID":"'.$productId.'",
				"productName":"'.$productName.'",
				"productDescription":"'. str_replace("'", "\'", $productDescription).'",
				"productURL":"'.$productURL.'",
				"productImageURL":"'.$productImageURL.'",
				"productPrice":"'.$productPrice.'"
			}]);
			</script>'."\n";
		}
		
		return $CartStackCartContentsCode;
		
	}
	
}
