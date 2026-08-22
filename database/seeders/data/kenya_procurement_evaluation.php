<?php

return [
    'vendor_categories' => [
        ['name' => 'ICT and Digital Services', 'description' => 'Software, data, connectivity and digital transformation services.'],
        ['name' => 'Professional and Advisory Services', 'description' => 'Programme design, policy, legal and management advisory services.'],
        ['name' => 'Construction and Infrastructure', 'description' => 'Civil works, facilities, engineering and infrastructure services.'],
        ['name' => 'Goods and General Supplies', 'description' => 'Equipment, consumables, furniture and general supply contracts.'],
        ['name' => 'Monitoring, Evaluation and Audit', 'description' => 'Independent assurance, audit, verification and evaluation services.'],
        ['name' => 'Logistics and Facilities Services', 'description' => 'Transport, warehousing, events, maintenance and facilities support.'],
    ],

    // Reserved example.invalid identities make it explicit that these are
    // synthetic vendors and cannot be mistaken for real Kenyan companies.
    'vendors' => [
        ['name' => 'Kenya Demo Vendor 01 - Acacia Digital Works', 'category' => 'ICT and Digital Services'],
        ['name' => 'Kenya Demo Vendor 02 - Rift Data Systems', 'category' => 'ICT and Digital Services'],
        ['name' => 'Kenya Demo Vendor 03 - Tana Cloud Services', 'category' => 'ICT and Digital Services'],
        ['name' => 'Kenya Demo Vendor 04 - Savannah Civic Technology', 'category' => 'ICT and Digital Services'],
        ['name' => 'Kenya Demo Vendor 05 - Jirani Advisory Group', 'category' => 'Professional and Advisory Services'],
        ['name' => 'Kenya Demo Vendor 06 - Pamoja Programme Partners', 'category' => 'Professional and Advisory Services'],
        ['name' => 'Kenya Demo Vendor 07 - Ufanisi Delivery Advisors', 'category' => 'Professional and Advisory Services'],
        ['name' => 'Kenya Demo Vendor 08 - Horizon Policy Services', 'category' => 'Professional and Advisory Services'],
        ['name' => 'Kenya Demo Vendor 09 - Imara Civil Works', 'category' => 'Construction and Infrastructure'],
        ['name' => 'Kenya Demo Vendor 10 - Mwangaza Engineering', 'category' => 'Construction and Infrastructure'],
        ['name' => 'Kenya Demo Vendor 11 - Highlands Infrastructure Services', 'category' => 'Construction and Infrastructure'],
        ['name' => 'Kenya Demo Vendor 12 - Coastline Works Consortium', 'category' => 'Construction and Infrastructure'],
        ['name' => 'Kenya Demo Vendor 13 - Taifa General Supplies', 'category' => 'Goods and General Supplies'],
        ['name' => 'Kenya Demo Vendor 14 - Karibu Equipment Supply', 'category' => 'Goods and General Supplies'],
        ['name' => 'Kenya Demo Vendor 15 - Equator Office Solutions', 'category' => 'Goods and General Supplies'],
        ['name' => 'Kenya Demo Vendor 16 - Lakeview Trade Services', 'category' => 'Goods and General Supplies'],
        ['name' => 'Kenya Demo Vendor 17 - Uwajibikaji Assurance', 'category' => 'Monitoring, Evaluation and Audit'],
        ['name' => 'Kenya Demo Vendor 18 - Matokeo Evaluation Services', 'category' => 'Monitoring, Evaluation and Audit'],
        ['name' => 'Kenya Demo Vendor 19 - Thabiti Audit Partners', 'category' => 'Monitoring, Evaluation and Audit'],
        ['name' => 'Kenya Demo Vendor 20 - Evidence Track Africa Demo', 'category' => 'Monitoring, Evaluation and Audit'],
        ['name' => 'Kenya Demo Vendor 21 - Barabara Logistics', 'category' => 'Logistics and Facilities Services'],
        ['name' => 'Kenya Demo Vendor 22 - Bandari Facilities Support', 'category' => 'Logistics and Facilities Services'],
        ['name' => 'Kenya Demo Vendor 23 - Nairobi Demo Logistics', 'category' => 'Logistics and Facilities Services'],
        ['name' => 'Kenya Demo Vendor 24 - Northern Corridor Support', 'category' => 'Logistics and Facilities Services'],
    ],

    'application_fields' => [
        ['key' => 'official_name', 'label' => 'Registered vendor name', 'type' => 'text', 'required' => true],
        ['key' => 'official_email', 'label' => 'Official vendor email', 'type' => 'email', 'required' => true],
        ['key' => 'business_registration_number', 'label' => 'Business registration number', 'type' => 'text', 'required' => true],
        ['key' => 'tax_compliance_reference', 'label' => 'Tax compliance reference', 'type' => 'text', 'required' => true],
        ['key' => 'debarment_declaration', 'label' => 'Not debarred declaration', 'type' => 'select', 'required' => true, 'options' => ['Yes', 'No']],
        ['key' => 'signed_bid_declaration', 'label' => 'Signed bid declaration', 'type' => 'select', 'required' => true, 'options' => ['Yes', 'No']],
        ['key' => 'conflict_interest_declaration', 'label' => 'Conflict of interest declaration', 'type' => 'select', 'required' => true, 'options' => ['No conflict declared', 'Conflict disclosed']],
        ['key' => 'beneficial_ownership_disclosure', 'label' => 'Beneficial ownership disclosure', 'type' => 'select', 'required' => true, 'options' => ['Complete', 'Incomplete']],
        ['key' => 'relevant_experience_summary', 'label' => 'Relevant experience summary', 'type' => 'textarea', 'required' => true],
        ['key' => 'key_personnel_summary', 'label' => 'Key personnel summary', 'type' => 'textarea', 'required' => true],
        ['key' => 'financial_capacity_declaration', 'label' => 'Financial capacity declaration', 'type' => 'textarea', 'required' => true],
        ['key' => 'implementation_period_days', 'label' => 'Implementation period in days', 'type' => 'number', 'required' => true],
        ['key' => 'bid_amount_kes', 'label' => 'Bid amount (KES)', 'type' => 'number', 'required' => true],
    ],

    'prescreening_sections' => [
        [
            'key' => 'legal',
            'name' => 'Legal and Statutory Compliance',
            'criteria' => [
                ['key' => 'business_registration_number', 'name' => 'Valid business registration supplied'],
                ['key' => 'tax_compliance_reference', 'name' => 'Valid tax compliance reference supplied'],
                ['key' => 'debarment_declaration', 'name' => 'Vendor confirms it is not debarred'],
            ],
        ],
        [
            'key' => 'administrative',
            'name' => 'Administrative Completeness',
            'criteria' => [
                ['key' => 'signed_bid_declaration', 'name' => 'Bid declaration is complete and signed'],
                ['key' => 'conflict_interest_declaration', 'name' => 'Conflict of interest declaration is acceptable'],
                ['key' => 'beneficial_ownership_disclosure', 'name' => 'Beneficial ownership disclosure is complete'],
            ],
        ],
        [
            'key' => 'capacity',
            'name' => 'Minimum Capacity Gate',
            'criteria' => [
                ['key' => 'relevant_experience_summary', 'name' => 'Minimum relevant experience demonstrated'],
                ['key' => 'key_personnel_summary', 'name' => 'Minimum key personnel capacity demonstrated'],
                ['key' => 'financial_capacity_declaration', 'name' => 'Minimum financial capacity demonstrated'],
            ],
        ],
    ],

    'evaluation_phases' => [
        'technical' => [
            'name' => 'Technical Evaluation',
            'sections' => [
                [
                    'key' => 'methodology',
                    'name' => 'Methodology and Technical Approach',
                    'criteria' => [
                        ['key' => 'methodology', 'name' => 'Understanding and proposed methodology', 'max_score' => 20],
                        ['key' => 'work_plan', 'name' => 'Work plan and delivery schedule', 'max_score' => 15],
                        ['key' => 'quality', 'name' => 'Quality assurance approach', 'max_score' => 15],
                    ],
                ],
                [
                    'key' => 'capacity',
                    'name' => 'Experience and Delivery Capacity',
                    'criteria' => [
                        ['key' => 'experience', 'name' => 'Relevant organizational experience', 'max_score' => 20],
                        ['key' => 'personnel', 'name' => 'Qualifications of key personnel', 'max_score' => 15],
                        ['key' => 'risk', 'name' => 'Risk management and sustainability', 'max_score' => 15],
                    ],
                ],
            ],
        ],
        'financial' => [
            'name' => 'Financial Evaluation',
            'sections' => [
                [
                    'key' => 'price',
                    'name' => 'Price and Cost Assessment',
                    'criteria' => [
                        ['key' => 'competitiveness', 'name' => 'Price competitiveness', 'max_score' => 35],
                        ['key' => 'realism', 'name' => 'Cost realism and completeness', 'max_score' => 25],
                    ],
                ],
                [
                    'key' => 'capacity',
                    'name' => 'Financial Capacity and Value',
                    'criteria' => [
                        ['key' => 'capacity', 'name' => 'Financial capacity and liquidity', 'max_score' => 20],
                        ['key' => 'value', 'name' => 'Whole-life value for money', 'max_score' => 20],
                    ],
                ],
            ],
        ],
    ],
];
