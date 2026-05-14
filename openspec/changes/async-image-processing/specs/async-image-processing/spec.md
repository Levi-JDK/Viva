# Spec: Async Image Processing (NEW)

## Purpose

Worker-side image variant generation and temp file lifecycle management. Replaces synchronous GD processing during HTTP upload with async processing in ProductValidationJob.

## Requirements

### Requirement: Temp Image Storage on Upload

The system MUST save the raw uploaded file to `images/products/temp/{uuid}` instead of processing variants synchronously during the HTTP request.

#### Scenario: Valid image saved to temp

- GIVEN a producer submits a product with a valid image file
- WHEN upload_product.php processes the submission
- THEN the raw file is saved to `images/products/temp/{uuid}` and its relative path stored in `tab_imagenes.url_imagen`
- AND no WebP conversion or variant generation occurs during the HTTP request

### Requirement: Product Status pending_images on Creation

The system MUST create new products with `validation_status = 'pending_images'` and `is_active = false`.

#### Scenario: New product starts as pending_images

- GIVEN a producer submits a valid product form
- WHEN the product record is inserted
- THEN `validation_status` is `'pending_images'` and `is_active` is `false`
- AND the product is enqueued with `temp_path` in the payload

### Requirement: Worker Image Processing Step

ProductValidationJob MUST execute image processing before AI validation.

#### Scenario: Happy path — upload to pending_review

- GIVEN a producer uploads a valid product with a 3MB JPG
- WHEN upload_product.php saves the raw file to temp and creates the product as `pending_images`
- THEN the HTTP response returns immediately
- WHEN the worker picks up the job and processes images
- THEN the temp file moves to `images/products/`, variants are generated, `tab_imagenes` is updated, and status becomes `pending_review`

#### Scenario: Edge — image close to 5MB

- GIVEN a producer uploads a 4.8MB PNG image
- WHEN upload validation passes (under 5MB limit)
- THEN the file is saved to temp and product created as `pending_images`
- WHEN the worker processes images
- THEN variants are generated asynchronously with no HTTP timeout constraint

### Requirement: Orphan Cleanup Detection

The system SHOULD detect products stuck in `pending_images` status for longer than a configurable threshold (default: 1 hour).

#### Scenario: Stuck product detected after threshold

- GIVEN a product has `validation_status = 'pending_images'`
- WHEN the `created_at` timestamp exceeds the threshold from `ai.config` key `orphan_cleanup_minutes`
- THEN the product and its temp file are flagged for cleanup