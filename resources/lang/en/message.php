<?php

return [
    'error' => [
        'default' => 'An error has occurred, please try again later',
        'token' => 'Could not create token',
        'order' => [
            'exist' => 'order already registered',
            'user-create-order' => 'This user cannot update the order',
            'not-approved' => 'This order not approved yet',
        ],
        'cancel-order' => [
            'exist' => 'cancel order already registered',
            'user-create-order' => 'This user cannot update the cancel order',
        ]
    ],
    'success' => [
        'logout' => 'Successfully logged out',
        'order' => [
            'updated' => 'Order updated successfully',
        ]
    ],
];
