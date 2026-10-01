<?php

return [

    'dashboard' => [
        'label' => 'Dashboard',
    ],

    'ecommerce' => [

        'label' => 'Ecommerce Panel',

        'children' => [

            'customers' => [
                'label' => 'Customers',
            ],

            'categories' => [
                'label' => 'Categories',
            ],

            'products' => [
                'label' => 'Products',

                'actions' => [
                    'view'   => 'View',
                    'create' => 'Create',
                    'edit'   => 'Edit',
                    'delete' => 'Delete',
                ],
            ],

            'ecommerce_orders' => [
                'label' => 'Orders',

                'actions' => [
                    'view'   => 'View',
                    'status' => 'Status Change',
                    'cancel' => 'Cancel',
                ],
            ],

            'help_tickets' => [
                'label' => 'Help Tickets',

                'actions' => [
                    'view'  => 'View',
                    'reply' => 'Reply',
                    'close' => 'Close',
                ],
            ],

        ],
    ],

    'members' => [

        'label' => 'Members Panel',

        'children' => [

            'members' => [
                'label' => 'Members',

                'actions' => [
                    'view'   => 'View',
                    'create' => 'Create',
                    'edit'   => 'Edit',
                    'delete' => 'Delete',
                ],
            ],

            'member_kyc' => [
                'label' => 'Member KYC Verification',

                'actions' => [
                    'view'    => 'View',
                    'approve' => 'Approve',
                    'reject'  => 'Reject',
                ],
            ],

            'view_tree' => [
                'label' => 'View Tree',
            ],

            'member_balance_requests' => [
                'label' => 'Member Balance Requests',

                'actions' => [
                    'view'    => 'View',
                    'approve' => 'Approve',
                    'reject'  => 'Reject',
                ],
            ],

            'rank_achievers' => [
                'label' => 'Rank Achievers',
            ],

            'reward_achievers' => [
                'label' => 'Reward Achievers',
            ],

            'active_block' => [
                'label' => 'Active / Block',
            ],

            'daily_closing' => [
                'label' => 'Daily Closing',
            ],

            'member_orders' => [
                'label' => 'Member Orders',

                'actions' => [
                    'view'   => 'View',
                    'status' => 'Status Change',
                    'cancel' => 'Cancel',
                ],
            ],

        ],
    ],

    'shoppee' => [

        'label' => 'Shoppee Panel',

        'children' => [

            'add_branch' => [
                'label' => 'Add Branch',
            ],

            'branch_list' => [
                'label' => 'Branch List',

                'actions' => [
                    'view'   => 'View',
                    'create' => 'Create',
                    'edit'   => 'Edit',
                    'delete' => 'Delete',
                ],
            ],

            'shoppee_kyc' => [
                'label' => 'Shoppee KYC Verification',

                'actions' => [
                    'view'    => 'View',
                    'approve' => 'Approve',
                    'reject'  => 'Reject',
                ],
            ],

            'product_requests' => [
                'label' => 'Product Requests',

                'actions' => [
                    'view'    => 'View',
                    'approve' => 'Approve',
                    'reject'  => 'Reject',
                ],
            ],

            'shoppee_balance_requests' => [
                'label' => 'Shoppee Balance Requests',

                'actions' => [
                    'view'    => 'View',
                    'approve' => 'Approve',
                    'reject'  => 'Reject',
                ],
            ],

            'shoppee_helpdesk' => [
                'label' => 'Helpdesk',

                'actions' => [
                    'view'  => 'View',
                    'reply' => 'Reply',
                    'close' => 'Close',
                ],
            ],

        ],
    ],

];