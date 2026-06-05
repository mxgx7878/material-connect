<?php
// FILE PATH: config/disputes.php

return [
    /*
    |--------------------------------------------------------------------------
    | Supplier Workflow
    |--------------------------------------------------------------------------
    | When enabled, raised disputes are auto-assigned to the supplier and enter
    | the 48-hour supplier response window. When disabled, disputes go straight
    | to admin review and the supplier endpoints are bypassed.
    |
    | Flip to true once the supplier frontend ships.
    */
    'supplier_workflow_enabled' => env('DISPUTES_SUPPLIER_WORKFLOW', false),
];