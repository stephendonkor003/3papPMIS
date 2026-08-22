<?php

/**
 * Kenya national-government demo-data catalogue.
 *
 * Institution names are based on the current Cabinet listing and the national
 * government organisation sources checked on 2026-08-22. The money generated
 * from this catalogue is synthetic demonstration data, not an appropriation or
 * statement of actual Government of Kenya expenditure.
 */
return [
    'snapshot_date' => '2026-08-22',
    'sources' => [
        'https://www.president.go.ke/cabinet/',
        'https://www.president.go.ke/wp-content/uploads/Executive-Order-No.-2-of-2023-.pdf',
        'https://gok.kenya.go.ke/ministries',
        'https://gok.kenya.go.ke/agencies',
    ],

    'executive_offices' => [
        ['code' => 'KE-EOP', 'name' => 'Executive Office of the President'],
        ['code' => 'KE-ODP', 'name' => 'Office of the Deputy President'],
        ['code' => 'KE-OPCS', 'name' => 'Office of the Prime Cabinet Secretary'],
    ],

    'ministries' => [
        [
            'code' => 'KE-MIN-FDA',
            'name' => 'Ministry of Foreign and Diaspora Affairs',
            'portfolios' => ['Foreign Relations and Diplomacy', 'Diaspora Services and Engagement'],
            'departments' => [
                ['code' => 'KE-SD-FA', 'name' => 'State Department for Foreign Affairs', 'agencies' => ['Kenya Foreign Service Institute']],
                ['code' => 'KE-SD-DA', 'name' => 'State Department for Diaspora Affairs', 'agencies' => ['Diaspora Placement Agency']],
                ['code' => 'KE-SD-PA', 'name' => 'State Department for Parliamentary Affairs', 'agencies' => []],
            ],
        ],
        [
            'code' => 'KE-MIN-INA',
            'name' => 'Ministry of Interior and National Administration',
            'portfolios' => ['Internal Security and National Administration', 'Citizen, Immigration and Correctional Services'],
            'departments' => [
                ['code' => 'KE-SD-ISNA', 'name' => 'State Department for Internal Security and National Administration', 'agencies' => ['National Counter Terrorism Centre', 'National Crime Research Centre', 'National Cohesion and Integration Commission', 'Kenya Coast Guard Service']],
                ['code' => 'KE-SD-CS', 'name' => 'State Department for Correctional Services', 'agencies' => ['Kenya Prisons Service', 'Probation and Aftercare Service', 'Borstal Institutions']],
                ['code' => 'KE-SD-ICS', 'name' => 'State Department for Immigration and Citizen Services', 'agencies' => ['Directorate of Immigration Services', 'Civil Registration Services', 'National Registration Bureau', 'Department of Refugee Services']],
            ],
        ],
        [
            'code' => 'KE-MIN-DEF',
            'name' => 'Ministry of Defence',
            'portfolios' => ['National Defence Readiness', 'Defence Industry and Strategic Assets'],
            'departments' => [
                ['code' => 'KE-SD-DEF', 'name' => 'State Department for Defence', 'agencies' => ['Kenya Defence Forces', 'Kenya Space Agency', 'Defence Forces Canteen Organisation', 'Defence Forces Medical Insurance Scheme', 'Defence Forces Old Comrades Association', 'Kenya Ordnance Factories Corporation', 'Kenya Meat Commission', 'Kenya Shipyards Limited']],
            ],
        ],
        [
            'code' => 'KE-MIN-NTEP',
            'name' => 'The National Treasury and Economic Planning',
            'portfolios' => ['Public Finance and Exchequer Management', 'Economic Planning and Investment Coordination'],
            'departments' => [
                ['code' => 'KE-SD-NT', 'name' => 'State Department for the National Treasury', 'agencies' => ['Central Bank of Kenya', 'Financial Reporting Centre', 'Kenya Post Office Savings Bank', 'Kenya Revenue Authority', 'Nairobi International Financial Centre Authority', 'Public Procurement Regulatory Authority', 'Public Sector Accounting Standards Board', 'Unclaimed Financial Assets Authority', 'Capital Markets Authority', 'Consolidated Bank of Kenya', 'Insurance Regulatory Authority', 'Kenya Investment Authority', 'Kenya Reinsurance Corporation', 'National Bank of Kenya', 'Privatisation Authority', 'Retirement Benefits Authority', 'Competition Authority of Kenya', 'Kenya Deposit Insurance Corporation', 'KASNEB', 'KenTrade', 'Public Private Partnerships Directorate', 'Public Service Superannuation Scheme', 'Government Clearing Agency', 'Kenya Development Corporation']],
                ['code' => 'KE-SD-EP', 'name' => 'State Department for Economic Planning', 'agencies' => ['Competition Tribunal', 'Public Financial Management Reforms Secretariat', 'Kenya National Bureau of Statistics']],
            ],
        ],
        [
            'code' => 'KE-MIN-PSHCD',
            'name' => 'Ministry of Public Service and Human Capital Development',
            'portfolios' => ['Public Service Transformation', 'Human Capital and Government Delivery'],
            'departments' => [
                ['code' => 'KE-SD-PSHC', 'name' => 'State Department for Public Service and Human Capital Development', 'agencies' => ['Kenya School of Government', 'Huduma Kenya Secretariat', 'National Youth Service']],
                ['code' => 'KE-SD-PDM', 'name' => 'State Department for Performance and Delivery Management', 'agencies' => ['Government Delivery Unit']],
            ],
        ],
        [
            'code' => 'KE-MIN-RT',
            'name' => 'Ministry of Roads and Transport',
            'portfolios' => ['Road Infrastructure and Safety', 'Integrated Transport and Logistics'],
            'departments' => [
                ['code' => 'KE-SD-RDS', 'name' => 'State Department for Roads', 'agencies' => ['Kenya Roads Board', 'Kenya National Highways Authority', 'Kenya Urban Roads Authority', 'Kenya Rural Roads Authority', 'Kenya Institute of Highways and Building Technology', 'Engineers Board of Kenya']],
                ['code' => 'KE-SD-TRN', 'name' => 'State Department for Transport', 'agencies' => ['Kenya Civil Aviation Authority', 'Kenya Airports Authority', 'Kenya Ports Authority', 'Kenya Ferry Services', 'Kenya National Shipping Line', 'Kenya Railways Corporation', 'National Transport and Safety Authority', 'Northern Corridor Transit and Transport Coordination Authority', 'LAPSSET Corridor Development Authority', 'Nairobi Metropolitan Area Transport Authority', 'Railway City Development Authority']],
            ],
        ],
        [
            'code' => 'KE-MIN-LPHUD',
            'name' => 'Ministry of Lands, Public Works, Housing and Urban Development',
            'portfolios' => ['Land Administration and Spatial Planning', 'Housing, Urban Development and Public Works'],
            'departments' => [
                ['code' => 'KE-SD-LPP', 'name' => 'State Department for Lands and Physical Planning', 'agencies' => ['Kenya Institute of Surveying and Mapping']],
                ['code' => 'KE-SD-HUD', 'name' => 'State Department for Housing and Urban Development', 'agencies' => ['National Housing Corporation', 'National Housing Development Fund', 'Estate Agents Registration Board', 'Affordable Housing Board']],
                ['code' => 'KE-SD-PW', 'name' => 'State Department for Public Works', 'agencies' => ['National Construction Authority', 'Board of Registration of Architects and Quantity Surveyors', 'Building Surveyors Registration Board']],
            ],
        ],
        [
            'code' => 'KE-MIN-ICDE',
            'name' => 'Ministry of Information, Communications and the Digital Economy',
            'portfolios' => ['Digital Government and ICT Infrastructure', 'Broadcasting, Communications and Public Information'],
            'departments' => [
                ['code' => 'KE-SD-BT', 'name' => 'State Department for Broadcasting and Telecommunications', 'agencies' => ['Communications Authority of Kenya', 'Kenya Broadcasting Corporation', 'Postal Corporation of Kenya', 'Media Council of Kenya', 'Kenya Institute of Mass Communication', 'National Communication Secretariat', 'Kenya Yearbook Editorial Board', 'Kenya News Agency', 'Government Advertising Agency']],
                ['code' => 'KE-SD-ICT', 'name' => 'State Department for ICT and the Digital Economy', 'agencies' => ['ICT Authority of Kenya', 'Konza Technopolis Development Authority', 'Office of the Data Protection Commissioner']],
            ],
        ],
        [
            'code' => 'KE-MIN-HLT',
            'name' => 'Ministry of Health',
            'portfolios' => ['Universal Health Coverage and Medical Services', 'Public Health, Standards and Disease Prevention'],
            'departments' => [
                ['code' => 'KE-SD-MS', 'name' => 'State Department for Medical Services', 'agencies' => ['Social Health Authority', 'Kenya Medical Supplies Authority', 'Kenya Medical Research Institute', 'Kenyatta National Hospital', 'Moi Teaching and Referral Hospital', 'Kenya Medical Training College']],
                ['code' => 'KE-SD-PHPS', 'name' => 'State Department for Public Health and Professional Standards', 'agencies' => ['Pharmacy and Poisons Board', 'Public Health Officers and Technicians Council', 'National Syndemic Diseases Control Council']],
            ],
        ],
        [
            'code' => 'KE-MIN-EDU',
            'name' => 'Ministry of Education',
            'portfolios' => ['Basic Education Quality and Access', 'Higher Education, TVET, Science and Innovation'],
            'departments' => [
                ['code' => 'KE-SD-BE', 'name' => 'State Department for Basic Education', 'agencies' => ['Kenya National Examinations Council', 'Kenya Education Management Institute', 'Kenya Literature Bureau', 'Kenya Institute of Special Education', 'Kenya Institute of Curriculum Development', 'School Equipment Production Unit', 'Jomo Kenyatta Foundation']],
                ['code' => 'KE-SD-TVET', 'name' => 'State Department for Technical and Vocational Education and Training', 'agencies' => ['Technical and Vocational Education and Training Authority', 'TVET Curriculum Development Assessment and Certification Council', 'Kenya National Qualifications Authority']],
                ['code' => 'KE-SD-HER', 'name' => 'State Department for Higher Education and Research', 'agencies' => ['Higher Education Loans Board', 'Commission for University Education', 'Kenya Universities and Colleges Central Placement Service', 'Universities Fund']],
                ['code' => 'KE-SD-SRI', 'name' => 'State Department for Science, Research and Innovation', 'agencies' => ['National Commission for Science, Technology and Innovation', 'Kenya National Innovation Agency', 'National Research Fund', 'National Biosafety Authority', 'Kenya National Commission for UNESCO', 'Centre for Mathematics, Science and Technology Education in Africa']],
            ],
        ],
        [
            'code' => 'KE-MIN-ALD',
            'name' => 'Ministry of Agriculture and Livestock Development',
            'portfolios' => ['Crop Development and Food Security', 'Livestock Development and Agricultural Research'],
            'departments' => [
                ['code' => 'KE-SD-CD', 'name' => 'State Department for Crop Development', 'agencies' => ['Agriculture and Food Authority', 'National Cereals and Produce Board', 'Kenya Plant Health Inspectorate Service', 'Pest Control Products Board', 'Kenya Seed Company']],
                ['code' => 'KE-SD-LD', 'name' => 'State Department for Livestock Development', 'agencies' => ['Kenya Agricultural and Livestock Research Organisation', 'Kenya Dairy Board', 'Kenya Veterinary Vaccines Production Institute', 'Kenya Animal Genetic Resources Centre']],
            ],
        ],
        [
            'code' => 'KE-MIN-ITI',
            'name' => 'Ministry of Investments, Trade and Industry',
            'portfolios' => ['Investment and Industrial Development', 'Domestic and International Trade'],
            'departments' => [
                ['code' => 'KE-SD-IP', 'name' => 'State Department for Investment Promotion', 'agencies' => ['Export Processing Zones Authority', 'Special Economic Zones Authority']],
                ['code' => 'KE-SD-IND', 'name' => 'State Department for Industry', 'agencies' => ['Kenya Bureau of Standards', 'Kenya Industrial Property Institute', 'Kenya Industrial Research and Development Institute', 'Kenya Industrial Estates']],
                ['code' => 'KE-SD-TRD', 'name' => 'State Department for Trade', 'agencies' => ['Kenya Trade Remedies Agency', 'Anti-Counterfeit Authority', 'Kenya Export Promotion and Branding Agency', 'Kenya National Trading Corporation', 'Kenya Trade Portal', 'Kenya Commodities Exchange']],
            ],
        ],
        [
            'code' => 'KE-MIN-CMSME',
            'name' => 'Ministry of Co-operatives and Micro, Small and Medium Enterprises Development',
            'portfolios' => ['Co-operative Development and Regulation', 'MSME Finance and Enterprise Growth'],
            'departments' => [
                ['code' => 'KE-SD-COOP', 'name' => 'State Department for Co-operatives', 'agencies' => ['Sacco Societies Regulatory Authority', 'Co-operative Tribunal', 'New Kenya Planters Co-operative Union']],
                ['code' => 'KE-SD-MSME', 'name' => 'State Department for Micro, Small and Medium Enterprises Development', 'agencies' => ['Micro and Small Enterprises Authority', 'Financial Inclusion Fund', 'Uwezo Fund']],
            ],
        ],
        [
            'code' => 'KE-MIN-YCES',
            'name' => 'Ministry of Youth Affairs, Creative Economy and Sports',
            'portfolios' => ['Youth and Creative Economy Development', 'Sports Development and Integrity'],
            'departments' => [
                ['code' => 'KE-SD-YCE', 'name' => 'State Department for Youth Affairs and the Creative Economy', 'agencies' => ['National Youth Council', 'Youth Enterprise Development Fund']],
                ['code' => 'KE-SD-SPT', 'name' => 'State Department for Sports', 'agencies' => ['Sports Kenya', 'Anti-Doping Agency of Kenya', 'Kenya Academy of Sports', 'Sports, Arts and Social Development Fund']],
            ],
        ],
        [
            'code' => 'KE-MIN-ECCF',
            'name' => 'Ministry of Environment, Climate Change and Forestry',
            'portfolios' => ['Environment and Climate Action', 'Forestry and Landscape Restoration'],
            'departments' => [
                ['code' => 'KE-SD-ECC', 'name' => 'State Department for Environment and Climate Change', 'agencies' => ['National Environment Management Authority', 'Kenya Meteorological Department', 'Kenya Water Towers Agency', 'National Environment Trust Fund', 'National Environment Tribunal']],
                ['code' => 'KE-SD-FOR', 'name' => 'State Department for Forestry', 'agencies' => ['Kenya Forest Service', 'Kenya Forestry Research Institute']],
            ],
        ],
        [
            'code' => 'KE-MIN-TW',
            'name' => 'Ministry of Tourism and Wildlife',
            'portfolios' => ['Tourism Development and Destination Marketing', 'Wildlife Conservation and Research'],
            'departments' => [
                ['code' => 'KE-SD-TOU', 'name' => 'State Department for Tourism', 'agencies' => ['Tourism Regulatory Authority', 'Kenya Utalii College', 'Tourism Fund', 'Tourism Research Institute', 'Kenya Tourism Board', 'Kenyatta International Convention Centre']],
                ['code' => 'KE-SD-WLD', 'name' => 'State Department for Wildlife', 'agencies' => ['Kenya Wildlife Service', 'Wildlife Research and Training Institute']],
            ],
        ],
        [
            'code' => 'KE-MIN-WSI',
            'name' => 'Ministry of Water, Sanitation and Irrigation',
            'portfolios' => ['Water and Sanitation Services', 'Irrigation and Water Security'],
            'departments' => [
                ['code' => 'KE-SD-WS', 'name' => 'State Department for Water and Sanitation', 'agencies' => ['Water Services Regulatory Board', 'Water Resources Authority', 'Water Sector Trust Fund', 'Athi Water Works Development Agency', 'Coast Water Works Development Agency', 'Lake Victoria North Water Works Development Agency', 'Lake Victoria South Water Works Development Agency', 'North Rift Valley Water Works Development Agency', 'Northern Water Works Development Agency', 'Tana Water Works Development Agency', 'Tanathi Water Works Development Agency', 'Central Rift Valley Water Works Development Agency']],
                ['code' => 'KE-SD-IRR', 'name' => 'State Department for Irrigation', 'agencies' => ['National Irrigation Authority', 'Hydrologists Registration Board']],
            ],
        ],
        [
            'code' => 'KE-MIN-EP',
            'name' => 'Ministry of Energy and Petroleum',
            'portfolios' => ['Electricity and Renewable Energy', 'Petroleum and Strategic Energy Security'],
            'departments' => [
                ['code' => 'KE-SD-ENE', 'name' => 'State Department for Energy', 'agencies' => ['Energy and Petroleum Regulatory Authority', 'Kenya Power and Lighting Company', 'Kenya Electricity Generating Company', 'Kenya Electricity Transmission Company', 'Rural Electrification and Renewable Energy Corporation', 'Geothermal Development Company', 'Nuclear Power and Energy Agency']],
                ['code' => 'KE-SD-PET', 'name' => 'State Department for Petroleum', 'agencies' => ['National Oil Corporation of Kenya', 'Kenya Pipeline Company', 'Petroleum Institute of East Africa']],
            ],
        ],
        [
            'code' => 'KE-MIN-LSP',
            'name' => 'Ministry of Labour and Social Protection',
            'portfolios' => ['Labour, Employment and Skills Development', 'Social Protection and Inclusion'],
            'departments' => [
                ['code' => 'KE-SD-LSD', 'name' => 'State Department for Labour and Skills Development', 'agencies' => ['National Industrial Training Authority', 'National Employment Authority', 'Directorate of Occupational Safety and Health Services']],
                ['code' => 'KE-SD-SPSCA', 'name' => 'State Department for Social Protection and Senior Citizen Affairs', 'agencies' => ['National Social Security Fund', 'National Council for Persons with Disabilities', 'National Social Assistance Authority', 'Street Families Rehabilitation Trust Fund']],
            ],
        ],
        [
            'code' => 'KE-MIN-EACRA',
            'name' => 'Ministry of East African Community and Regional Affairs',
            'portfolios' => ['East African Community Integration', 'ASAL and Regional Development'],
            'departments' => [
                ['code' => 'KE-SD-EAC', 'name' => 'State Department for East African Community Affairs', 'agencies' => ['East African Community Directorate']],
                ['code' => 'KE-SD-ASAL', 'name' => 'State Department for ASALs and Regional Development', 'agencies' => ['National Drought Management Authority', 'Coast Development Authority', 'Ewaso Ngiro North Development Authority', 'Ewaso Ngiro South Development Authority', 'Kerio Valley Development Authority', 'Lake Basin Development Authority', 'Tana and Athi Rivers Development Authority']],
            ],
        ],
        [
            'code' => 'KE-MIN-MBEMA',
            'name' => 'Ministry of Mining, Blue Economy and Maritime Affairs',
            'portfolios' => ['Mining and Mineral Development', 'Blue Economy, Fisheries and Maritime Services'],
            'departments' => [
                ['code' => 'KE-SD-MIN', 'name' => 'State Department for Mining', 'agencies' => ['National Mining Corporation', 'Geological Survey of Kenya', 'Mineral Rights Board']],
                ['code' => 'KE-SD-BEF', 'name' => 'State Department for Blue Economy and Fisheries', 'agencies' => ['Kenya Fisheries Service', 'Kenya Marine and Fisheries Research Institute', 'Fish Marketing Authority']],
                ['code' => 'KE-SD-SMA', 'name' => 'State Department for Shipping and Maritime Affairs', 'agencies' => ['Kenya Maritime Authority', 'Bandari Maritime Academy']],
            ],
        ],
        [
            'code' => 'KE-MIN-GEN',
            'name' => 'Ministry of Gender',
            'portfolios' => ['Gender Equality and Affirmative Action', 'Culture, Arts and National Heritage'],
            'departments' => [
                ['code' => 'KE-SD-GAA', 'name' => 'State Department for Gender and Affirmative Action', 'agencies' => ['National Government Affirmative Action Fund', 'Anti-FGM Board', 'Women Enterprise Fund']],
                ['code' => 'KE-SD-CAH', 'name' => 'State Department for Culture, the Arts and Heritage', 'agencies' => ['National Museums of Kenya', 'Kenya National Library Service', 'Kenya Copyright Board', 'Kenya Cultural Centre', 'National Heroes Council']],
            ],
        ],
        [
            'code' => 'KE-SLO',
            'name' => 'State Law Office and Department of Justice',
            'portfolios' => ['Justice, Legal Policy and State Law Services'],
            'departments' => [
                ['code' => 'KE-SD-JUST', 'name' => 'State Department for Justice and State Law', 'agencies' => ['Kenya Law Reform Commission', 'Council of Legal Education', 'Kenya School of Law', 'Witness Protection Agency', 'Business Registration Service', 'Assets Recovery Agency', 'National Council for Law Reporting']],
            ],
        ],
    ],

    'independent_institutions' => [
        ['code' => 'KE-PARL', 'name' => 'Parliament of Kenya', 'portfolio' => 'Legislative Services and Parliamentary Oversight'],
        ['code' => 'KE-JUD', 'name' => 'Judiciary of Kenya', 'portfolio' => 'Administration of Justice and Court Services'],
        ['code' => 'KE-PSC', 'name' => 'Public Service Commission', 'portfolio' => 'Public Service Standards and Human Resource Oversight'],
        ['code' => 'KE-OAG', 'name' => 'Office of the Auditor-General', 'portfolio' => 'Public Audit and Assurance'],
        ['code' => 'KE-COB', 'name' => 'Office of the Controller of Budget', 'portfolio' => 'Budget Implementation Oversight'],
        ['code' => 'KE-CRA', 'name' => 'Commission on Revenue Allocation', 'portfolio' => 'Intergovernmental Revenue Allocation'],
        ['code' => 'KE-SRC', 'name' => 'Salaries and Remuneration Commission', 'portfolio' => 'Public Sector Remuneration Governance'],
        ['code' => 'KE-NLC', 'name' => 'National Land Commission', 'portfolio' => 'Public Land Governance'],
        ['code' => 'KE-IEBC', 'name' => 'Independent Electoral and Boundaries Commission', 'portfolio' => 'Electoral Administration and Boundaries'],
        ['code' => 'KE-JSC', 'name' => 'Judicial Service Commission', 'portfolio' => 'Judicial Service Governance'],
        ['code' => 'KE-TSC', 'name' => 'Teachers Service Commission', 'portfolio' => 'Teacher Management and Professional Standards'],
        ['code' => 'KE-NPSC', 'name' => 'National Police Service Commission', 'portfolio' => 'Police Human Resource Oversight'],
        ['code' => 'KE-KNCHR', 'name' => 'Kenya National Commission on Human Rights', 'portfolio' => 'Human Rights Protection and Promotion'],
        ['code' => 'KE-NGEC', 'name' => 'National Gender and Equality Commission', 'portfolio' => 'Equality and Inclusion Oversight'],
        ['code' => 'KE-CAJ', 'name' => 'Commission on Administrative Justice', 'portfolio' => 'Administrative Justice and Ombudsman Services'],
        ['code' => 'KE-EACC', 'name' => 'Ethics and Anti-Corruption Commission', 'portfolio' => 'Integrity and Anti-Corruption'],
        ['code' => 'KE-ODPP', 'name' => 'Office of the Director of Public Prosecutions', 'portfolio' => 'Public Prosecution Services'],
    ],
];
