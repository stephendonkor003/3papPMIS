<?php

namespace App\Imports;

use App\Models\ThinkDataset;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ThinkDatasetImport implements ToModel, WithHeadingRow
{
    public function model(array $row): ThinkDataset
    {
        // Convert Excel serial date to proper Y-m-d format
        $dateFounded = $row['date_founded'] ?? null;
        if (is_numeric($dateFounded)) {
            try {
                $dateFounded = Date::excelToDateTimeObject($dateFounded)->format('Y-m-d');
            } catch (\Exception $e) {
                $dateFounded = null;
            }
        }


        return new ThinkDataset([
            'ottd_id' => $row['ottd_id'] ?? null,
            'tt_name_en' => $row['tt_name_en'] ?? null,
            'country' => $row['country'] ?? null,
            'continent' => $row['continent'] ?? null,
            'sub_region' => $row['sub_region'] ?? null,
            'Count' => $row['count'] ?? null,
            'website' => $row['website'] ?? null,
            'g_email' => $row['g_email'] ?? null,
            'operating_langs' => $row['operating_langs'] ?? null,
            'tt_init' => $row['tt_init'] ?? null,
            'description' => $row['description'] ?? null,
            'main_city' => $row['main_city'] ?? null,
            'Region_group' => $row['region_group'] ?? null,
            'other_offices' => $row['other_offices'] ?? null,
            'address' => $row['address'] ?? null,
            'tt_business_model' => $row['tt_business_model'] ?? null,
            'Funding_sources' => $row['funding_sources'] ?? null,
            'Funding_Mechanism' => $row['funding_mechanism'] ?? null,
            'tt_affiliations' => $row['tt_affiliations'] ?? null,
            'topics' => $row['topics'] ?? null,
            'geographies' => $row['geographies'] ?? null,
            'date_founded' => $dateFounded,
            'Date_founded_groups' => $row['date_founded_groups'] ?? null,
            'founder' => $row['founder'] ?? null,
            'founder_gender' => $row['founder_gender'] ?? null,
            'founder_other_type' => $row['founder_other_type'] ?? null,
            'staff_no' => $row['staff_no'] ?? null,
            'pc_staff_female' => $row['pc_staff_female'] ?? null,
            'pc_res_staff_female' => $row['pc_res_staff_female'] ?? null,
            'assc_no' => $row['assc_no'] ?? null,
            'assc_female_no' => $row['assc_female_no'] ?? null,
            'pub_no' => $row['pub_no'] ?? null,
            'fin_usd' => $row['fin_usd'] ?? null,
            'twitter_handle_link' => $row['twitter_handle_link'] ?? null,
            'facebook_page' => $row['facebook_page'] ?? null,
            'youtube_page' => $row['youtube_page'] ?? null,
            'instagram_acc' => $row['instagram_acc'] ?? null,
            'linkedIn_acc' => $row['linkedin_acc'] ?? null,
        ]);
    }
}
