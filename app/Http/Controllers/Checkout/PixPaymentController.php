<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\LiberpaySale;
use App\Models\FullPixSale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PixPaymentController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $paymentCode = $request->query('paymentcode');
        
        if (!$paymentCode) {
            abort(404, 'Código de pagamento não encontrado');
        }
        
        // Buscar venda pelo código PIX (pode ser Liberpay ou FullPix)
        $liberpaySale = LiberpaySale::where('pix_qr_code', $paymentCode)->first();
        $fullpixSale = FullPixSale::where('pix_qrcode', $paymentCode)->first();
        
        $sale = $liberpaySale ?? $fullpixSale;
        
        if (!$sale) {
            abort(404, 'Pagamento não encontrado');
        }
        
        // Determinar qual adquirente
        $acquirerSlug = $liberpaySale ? 'liberpay' : 'fullpix';
        
        // Buscar checkout do metadata
        $checkout = null;
        if (isset($sale->metadata['checkout_id'])) {
            $checkout = \App\Models\Checkout::with('product')->find($sale->metadata['checkout_id']);
        }
        
        // Preparar dados da venda
        $saleData = [
            'id' => $sale->id,
            'sale_id' => $liberpaySale ? $liberpaySale->liberpay_sale_id : $fullpixSale->fullpix_transaction_id,
            'pix_qr_code' => $liberpaySale ? $liberpaySale->pix_qr_code : $fullpixSale->pix_qrcode,
            'pix_qr_code_image' => $liberpaySale ? $liberpaySale->pix_qr_code_image : $fullpixSale->pix_qrcode_image,
            'expires_at' => $sale->expires_at?->toIso8601String(),
            'amount' => (float) $sale->amount,
            'acquirer' => $acquirerSlug,
        ];
        
        return Inertia::render('Checkout/AwaitingPayment', [
            'checkout' => $checkout,
            'sale' => $saleData,
        ]);
    }
}

