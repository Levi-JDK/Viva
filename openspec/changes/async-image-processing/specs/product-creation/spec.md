# Delta for product-creation

## Purpose

Product creation via HTTP upload. Modified to use temp storage and `pending_images` status instead of synchronous image processing.

## MODIFIED Requirements

### Requirement: Upload No Longer Blocks on GD Operations

The upload endpoint MUST NOT perform WebP conversion or variant generation synchronously.

(Previously: upload_product.php called `processAndUploadImages()` synchronously, blocking the HTTP response until all GD operations completed.)

#### Scenario: Upload returns immediately after temp save

- GIVEN a producer submits a product with a valid image
- WHEN upload_product.php processes the request
- THEN only `saveTempUpload()` is called and the HTTP response returns before any variant generation

### Requirement: Upload Validation Remains Synchronous

Input validation (format, size, MIME type) MUST still occur before product creation. Invalid images are rejected immediately.

#### Scenario: Invalid image rejected before product creation

- GIVEN a producer submits an invalid image (wrong format, exceeds 5MB, or corrupted)
- WHEN upload_product.php validates the file
- THEN the upload is rejected with an error response and no product is created

### Requirement: Temp Path in Enqueue Payload

`viva_enqueue_product_validation()` MUST include `temp_path` in the job payload.

#### Scenario: Payload includes temp_path

- GIVEN a product is created with `pending_images` status
- WHEN the product is enqueued for validation
- THEN the payload includes `temp_path` pointing to the file in `images/products/temp/`