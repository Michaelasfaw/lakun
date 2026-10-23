<?php
/**
 * ReceiptParserService
 *
 * Server-side fallback: decodes a QR code from a receipt image (pure PHP).
 * Requires vendor/ folder — run `composer install` locally, then upload vendor/.
 *
 * The primary QR decoding happens in the browser via jsQR (no server needed).
 * This endpoint is only called if the browser decode fails.
 */
class ReceiptParserService
{
    /**
     * @param  string $imagePath Absolute path to the uploaded temp image.
     * @return array{transaction_id:?string, sender_name:?string, payment_method:?string, source:?string, raw_text:string}
     */
    public static function parse(string $imagePath): array
    {
        $empty = [
            'transaction_id' => null,
            'sender_name'    => null,
            'payment_method' => null,
            'source'         => null,
            'raw_text'       => '',
        ];

        $qrText = self::tryQRCode($imagePath);
        if (!$qrText) {
            return $empty;
        }

        $extracted = self::extractFromText($qrText);
        return array_merge($empty, $extracted, ['source' => 'qr', 'raw_text' => $qrText]);
    }

    private static function tryQRCode(string $imagePath): ?string
    {
        $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (!file_exists($autoload)) {
            return null;
        }
        try {
            require_once $autoload;
            if (!class_exists('\\Zxing\\QrReader')) {
                return null;
            }
            $text = (new \Zxing\QrReader($imagePath))->text();
            return ($text && strlen(trim($text)) > 3) ? trim($text) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function extractFromText(string $text): array
    {
        $result = [
            'transaction_id' => null,
            'sender_name'    => null,
            'payment_method' => null,
        ];

        if (preg_match('/telebirr|ethio\s*telecom/i', $text)) {
            $result['payment_method'] = 'telebirr';
        } elseif (preg_match('/commercial\s*bank|CBE\b/i', $text)) {
            $result['payment_method'] = 'bank_transfer';
        }

        $txnPatterns = [
            '/transaction\s*(?:id|no|number|ref)[:\s#.]*([A-Z0-9]{6,30})/i',
            '/\b(FT[A-Z0-9]{8,18})\b/',
            '/ref(?:erence)?\s*(?:no|#|id)?[:\s.]*([A-Z0-9]{6,30})/i',
            '/(?:txn|trx|trans(?:action)?)\s*(?:no|id|#)?[:\s.]*([A-Z0-9]{6,30})/i',
            '/\b([A-Z]{2,4}[0-9]{6,14})\b/',
            '/\b(\d{12,20})\b/',
        ];
        foreach ($txnPatterns as $p) {
            if (preg_match($p, $text, $m)) {
                $c = strtoupper(trim($m[1]));
                if (!preg_match('/^0+$/', $c)) { $result['transaction_id'] = $c; break; }
            }
        }

        $namePatterns = [
            '/debited\s+from\s+([A-Z][A-Z\s]{2,50})/i',
            '/payer\s*name[:\s]+([A-Za-z][a-zA-Z\s]{2,50})/i',
            '/(?:sender|account\s*holder|account\s*name)[:\s]+([A-Za-z][a-zA-Z\s]{2,50})/i',
        ];
        foreach ($namePatterns as $p) {
            if (preg_match($p, $text, $m)) {
                $name = trim(preg_replace('/\s+/', ' ', $m[1]));
                if (strlen($name) <= 60 && !preg_match('/\d/', $name) && str_word_count($name) <= 6) {
                    $result['sender_name'] = ucwords(strtolower($name));
                    break;
                }
            }
        }

        return $result;
    }
}
