<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * 장바구니 페이지를 관리하는 Livewire 컴포넌트
 * 장바구니 아이템 조회, 수량 조절, 삭제 등의 기능을 처리
 */
#[Title('Cart - NexParan')]
class CartPage extends Component
{
    /** @var array 장바구니에 담긴 상품 목록을 저장하는 배열 */
    public $cart_items = [];
    
    /** @var float|int 장바구니 상품들의 총 금액 */
    public $grand_total;
    
    /**
     * 컴포넌트 초기화 메소드
     * 페이지 로드 시 쿠키에서 장바구니 정보를 가져와 초기 상태 설정
     * 
     * @return void
     */
    public function mount() {
        // 쿠키에서 장바구니 아이템 정보 로드
        $this->cart_items = CartManagement::getCartItemsFromCookie();
        // 총 금액 계산
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
    }
    
    /**
     * 장바구니에서 특정 상품을 제거하는 메소드
     * 
     * @param int $product_id 제거할 상품의 ID
     * @return void
     */
    public function removeItem($product_id) {
        // 장바구니에서 상품 제거
        $this->cart_items = CartManagement::removeCartItem($product_id);
        // 총 금액 재계산
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
        // Navbar 컴포넌트에 장바구니 아이템 개수 업데이트 이벤트 발송
        $this->dispatch('update-cart-count', total_count: count($this->cart_items))->to(Navbar::class);
    }
    
    /**
     * 장바구니 상품의 수량을 증가시키는 메소드
     * 
     * @param int $product_id 수량을 증가시킬 상품의 ID
     * @return void
     */
    public function increaseQty($product_id) {
        // 상품 수량 증가
        $this->cart_items = CartManagement::incrementQuantityToCartItem($product_id);
        // 총 금액 재계산
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
    }
    
    /**
     * 장바구니 상품의 수량을 감소시키는 메소드
     * 
     * @param int $product_id 수량을 감소시킬 상품의 ID
     * @return void
     */
    public function decreaseQty($product_id) {
        // 상품 수량 감소
        $this->cart_items = CartManagement::decrementQuantityToCartItem($product_id);
        // 총 금액 재계산
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
    }
    
    /**
     * 장바구니 페이지 뷰를 렌더링하는 메소드
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.cart-page');
    }
}
