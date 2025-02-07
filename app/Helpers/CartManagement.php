<?php

namespace App\Helpers;

use App\Models\Product;
use Illuminate\Support\Facades\Cookie;

/**
 * 장바구니 관리를 위한 헬퍼 클래스
 * 쿠키 기반의 장바구니 기능을 제공하며, 상품 추가/수정/삭제 등을 처리
 */
class CartManagement {
    /**
     * 장바구니에 상품을 추가하는 메소드
     * 이미 존재하는 상품이면 수량을 증가시키고, 없으면 새로 추가
     * 
     * @param int $product_id 추가할 상품의 ID
     * @return int 장바구니에 담긴 총 상품 개수
     */
    static public function addItemToCart($product_id) {
        // 현재 장바구니 상품 목록 조회
        $cart_items = self::getCartItemsFromCookie();
        
        // 이미 존재하는 상품인지 확인
        $existing_item = null;
        foreach($cart_items as $key => $item) {
            if($item['product_id'] == $product_id) {
                $existing_item = $key;
                break;
            }
        }
        
        // 이미 존재하는 상품이면 수량만 증가
        if($existing_item !== null) {
            $cart_items[$existing_item]['quantity']++;
            $cart_items[$existing_item]['total_amount'] = 
                $cart_items[$existing_item]['quantity'] * $cart_items[$existing_item]['unit_amount'];
        } 
        // 새로운 상품이면 장바구니에 추가
        else {
            $product = Product::where('id', $product_id)->first(['id', 'name', 'price', 'images']);
            if($product) {
                $cart_items[] = [
                    'product_id' => $product_id,
                    'name' => $product->name,
                    'image' => $product->images[0],
                    'quantity' => 1,
                    'unit_amount' => $product->price,
                    'total_amount' => $product->price
                ];
            }
        }
        
        // 장바구니 정보를 쿠키에 저장
        self::addCartItemsToCookie($cart_items);
        
        return count($cart_items);
    }
    
    /**
     * 장바구니에 지정된 수량으로 상품을 추가하는 메소드
     * 
     * @param int $product_id 추가할 상품의 ID
     * @param int $qty 추가할 수량
     * @return int 장바구니에 담긴 총 상품 개수
     */
    static public function addItemToCartWithQty($product_id, $qty) {
        $cart_items = self::getCartItemsFromCookie();
        
        $existing_item = null;
        foreach($cart_items as $key => $item) {
            if($item['product_id'] == $product_id) {
                $existing_item = $key;
                break;
            }
        }
        
        if($existing_item !== null) {
            $cart_items[$existing_item]['quantity'] = $qty;
            $cart_items[$existing_item]['total_amount'] = 
                $cart_items[$existing_item]['quantity'] * $cart_items[$existing_item]['unit_amount'];
        } else {
            $product = Product::where('id', $product_id)->first(['id', 'name', 'price', 'images']);
            if($product) {
                $cart_items[] = [
                    'product_id' => $product_id,
                    'name' => $product->name,
                    'image' => $product->images[0],
                    'quantity' => $qty,
                    'unit_amount' => $product->price,
                    'total_amount' => $product->price * $qty
                ];
            }
        }
        
        self::addCartItemsToCookie($cart_items);
        
        return count($cart_items);
    }
    
    /**
     * 장바구니에서 상품을 제거하는 메소드
     * 
     * @param int $product_id 제거할 상품의 ID
     * @return array 업데이트된 장바구니 상품 목록
     */
    static public function removeCartItem($product_id) {
        $cart_items = self::getCartItemsFromCookie();
        
        foreach($cart_items as $key => $item) {
            if($item['product_id'] == $product_id) {
                unset($cart_items[$key]);
            }
        }
        
        self::addCartItemsToCookie($cart_items);
        
        return $cart_items;
    }    
    
    /**
     * 장바구니 정보를 쿠키에 저장하는 메소드
     * 
     * @param array $cart_items 저장할 장바구니 상품 목록
     * @return void
     */
    static public function addCartItemsToCookie($cart_items) {
        Cookie::queue('cart_items', json_encode($cart_items), 60 * 24 * 30); // 30일 유효기간
    }
    
    /**
     * 장바구니를 비우는 메소드
     * 
     * @return void
     */
    static public function clearCartItemsFromCookie() {
        Cookie::queue(Cookie::forget('cart_items'));
    }
    
    /**
     * 쿠키에서 장바구니 정보를 조회하는 메소드
     * 
     * @return array 장바구니 상품 목록
     */
    static public function getCartItemsFromCookie() {
        $cart_items = json_decode(Cookie::get('cart_items'), true);
        if(!$cart_items) {
            $cart_items = [];
        }
        
        return $cart_items;
    }
    
    /**
     * 장바구니 상품의 수량을 증가시키는 메소드
     * 
     * @param int $product_id 수량을 증가시킬 상품의 ID
     * @return array 업데이트된 장바구니 상품 목록
     */
    static public function incrementQuantityToCartItem($product_id) {
        $cart_items = self::getCartItemsFromCookie();
        
        foreach($cart_items as $key => $item) {
            if($item['product_id'] == $product_id) {
                $cart_items[$key]['quantity']++;
                $cart_items[$key]['total_amount'] = 
                    $cart_items[$key]['quantity'] * $cart_items[$key]['unit_amount'];
            }
        }
        
        self::addCartItemsToCookie($cart_items);
        
        return $cart_items;
    }
    
    /**
     * 장바구니 상품의 수량을 감소시키는 메소드
     * 최소 수량은 1개로 제한
     * 
     * @param int $product_id 수량을 감소시킬 상품의 ID
     * @return array 업데이트된 장바구니 상품 목록
     */
    static public function decrementQuantityToCartItem($product_id) {
        $cart_items = self::getCartItemsFromCookie();
        
        foreach($cart_items as $key => $item) {
            if($item['product_id'] == $product_id) {
                if($cart_items[$key]['quantity'] > 1) {
                    $cart_items[$key]['quantity']--;
                    $cart_items[$key]['total_amount'] = 
                        $cart_items[$key]['quantity'] * $cart_items[$key]['unit_amount'];
                }
            }
        }
        
        self::addCartItemsToCookie($cart_items);
        
        return $cart_items;
    }
    
    /**
     * 장바구니 상품들의 총 금액을 계산하는 메소드
     * 
     * @param array $items 계산할 장바구니 상품 목록
     * @return float 총 금액
     */
    static public function calculateGrandTotal($items) {
        return array_sum(array_column($items, 'total_amount'));
    }
}