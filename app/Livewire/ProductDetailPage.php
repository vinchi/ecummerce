<?php

namespace App\Livewire;

use App\Models\Product;
use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Product Detail - NexParan')]
class ProductDetailPage extends Component
{
    use LivewireAlert;
    
    public $slug;
    public $quantity = 1;
    
    public function mount($slug) {
        $this->slug = $slug;
    }
    
    public function increaseQty() {
        $this->quantity++;
    }
    
    public function decreaseQty() {
        if($this->quantity > 1) {
            $this->quantity--;
        }
    }
    
    public function addToCart($product_id) {
        $total_count = CartManagement::addItemToCart($product_id);
        
        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);
        
        $this->alert('success', '제품이 카트에 담겼습니다.', [
            'position' => 'top-end',
            'timer' => 3000,
            'toast' => true
        ]);
    }
    
    public function render()
    {
        return view('livewire.product-detail-page', [
            'product' => Product::where('slug', $this->slug)->firstOrFail()
        ]);
    }
}
