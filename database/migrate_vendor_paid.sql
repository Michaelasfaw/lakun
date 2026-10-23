-- Add vendor_paid status to orders ENUM
ALTER TABLE orders MODIFY COLUMN status ENUM(
  'requested','sent_to_vendors','bids_received','sent_to_customer',
  'awaiting_payment','payment_under_review','confirmed',
  'vendor_paid',
  'assigned','out_for_delivery','delivered','completed',
  'cancelled','expired','failed_payment','bidding',
  'awaiting_confirmation','picked_up','on_the_way'
) DEFAULT 'requested';

-- Vendor payment tracking columns
ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS vendor_paid_at      DATETIME     NULL AFTER confirmed_at,
  ADD COLUMN IF NOT EXISTS vendor_payment_ref  VARCHAR(100) NULL AFTER vendor_paid_at,
  ADD COLUMN IF NOT EXISTS vendor_payment_amount DECIMAL(10,2) NULL AFTER vendor_payment_ref;
