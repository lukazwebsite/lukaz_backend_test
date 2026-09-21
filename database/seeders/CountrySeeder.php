<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array(
            array(
              'id' => 1,
              'sortname' => 'AF',
              'name' => 'Afghanistan',
              'status' => 0,
              'phonecode' => 93,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 2,
              'sortname' => 'AL',
              'name' => 'Albania',
              'status' => 0,
              'phonecode' => 355,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 3,
              'sortname' => 'DZ',
              'name' => 'Algeria',
              'status' => 0,
              'phonecode' => 213,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 4,
              'sortname' => 'AS',
              'name' => 'American Samoa',
              'status' => 0,
              'phonecode' => 1684,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 5,
              'sortname' => 'AD',
              'name' => 'Andorra',
              'status' => 0,
              'phonecode' => 376,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 6,
              'sortname' => 'AO',
              'name' => 'Angola',
              'status' => 0,
              'phonecode' => 244,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 7,
              'sortname' => 'AI',
              'name' => 'Anguilla',
              'status' => 0,
              'phonecode' => 1264,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 8,
              'sortname' => 'AQ',
              'name' => 'Antarctica',
              'status' => 0,
              'phonecode' => 0,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 9,
              'sortname' => 'AG',
              'name' => 'Antigua And Barbuda',
              'status' => 0,
              'phonecode' => 1268,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 10,
              'sortname' => 'AR',
              'name' => 'Argentina',
              'status' => 0,
              'phonecode' => 54,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 11,
              'sortname' => 'AM',
              'name' => 'Armenia',
              'status' => 0,
              'phonecode' => 374,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 12,
              'sortname' => 'AW',
              'name' => 'Aruba',
              'status' => 0,
              'phonecode' => 297,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 13,
              'sortname' => 'AU',
              'name' => 'Australia',
              'status' => 0,
              'phonecode' => 61,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 14,
              'sortname' => 'AT',
              'name' => 'Austria',
              'status' => 0,
              'phonecode' => 43,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 15,
              'sortname' => 'AZ',
              'name' => 'Azerbaijan',
              'status' => 0,
              'phonecode' => 994,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 16,
              'sortname' => 'BS',
              'name' => 'Bahamas The',
              'status' => 0,
              'phonecode' => 1242,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 17,
              'sortname' => 'BH',
              'name' => 'Bahrain',
              'status' => 0,
              'phonecode' => 973,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 18,
              'sortname' => 'BD',
              'name' => 'Bangladesh',
              'status' => 1,
              'phonecode' => 880,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 19,
              'sortname' => 'BB',
              'name' => 'Barbados',
              'status' => 0,
              'phonecode' => 1246,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 20,
              'sortname' => 'BY',
              'name' => 'Belarus',
              'status' => 0,
              'phonecode' => 375,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 21,
              'sortname' => 'BE',
              'name' => 'Belgium',
              'status' => 0,
              'phonecode' => 32,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 22,
              'sortname' => 'BZ',
              'name' => 'Belize',
              'status' => 0,
              'phonecode' => 501,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 23,
              'sortname' => 'BJ',
              'name' => 'Benin',
              'status' => 0,
              'phonecode' => 229,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 24,
              'sortname' => 'BM',
              'name' => 'Bermuda',
              'status' => 0,
              'phonecode' => 1441,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 25,
              'sortname' => 'BT',
              'name' => 'Bhutan',
              'status' => 0,
              'phonecode' => 975,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 26,
              'sortname' => 'BO',
              'name' => 'Bolivia',
              'status' => 0,
              'phonecode' => 591,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 27,
              'sortname' => 'BA',
              'name' => 'Bosnia and Herzegovina',
              'status' => 0,
              'phonecode' => 387,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 28,
              'sortname' => 'BW',
              'name' => 'Botswana',
              'status' => 0,
              'phonecode' => 267,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 29,
              'sortname' => 'BV',
              'name' => 'Bouvet Island',
              'status' => 0,
              'phonecode' => 0,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 30,
              'sortname' => 'BR',
              'name' => 'Brazil',
              'status' => 0,
              'phonecode' => 55,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 31,
              'sortname' => 'IO',
              'name' => 'British Indian Ocean Territory',
              'status' => 0,
              'phonecode' => 246,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 32,
              'sortname' => 'BN',
              'name' => 'Brunei',
              'status' => 0,
              'phonecode' => 673,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 33,
              'sortname' => 'BG',
              'name' => 'Bulgaria',
              'status' => 0,
              'phonecode' => 359,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 34,
              'sortname' => 'BF',
              'name' => 'Burkina Faso',
              'status' => 0,
              'phonecode' => 226,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 35,
              'sortname' => 'BI',
              'name' => 'Burundi',
              'status' => 0,
              'phonecode' => 257,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 36,
              'sortname' => 'KH',
              'name' => 'Cambodia',
              'status' => 0,
              'phonecode' => 855,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 37,
              'sortname' => 'CM',
              'name' => 'Cameroon',
              'status' => 0,
              'phonecode' => 237,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 38,
              'sortname' => 'CA',
              'name' => 'Canada',
              'status' => 0,
              'phonecode' => 1,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 39,
              'sortname' => 'CV',
              'name' => 'Cape Verde',
              'status' => 0,
              'phonecode' => 238,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 40,
              'sortname' => 'KY',
              'name' => 'Cayman Islands',
              'status' => 0,
              'phonecode' => 1345,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 41,
              'sortname' => 'CF',
              'name' => 'Central African Republic',
              'status' => 0,
              'phonecode' => 236,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 42,
              'sortname' => 'TD',
              'name' => 'Chad',
              'status' => 0,
              'phonecode' => 235,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 43,
              'sortname' => 'CL',
              'name' => 'Chile',
              'status' => 0,
              'phonecode' => 56,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 44,
              'sortname' => 'CN',
              'name' => 'China',
              'status' => 0,
              'phonecode' => 86,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 45,
              'sortname' => 'CX',
              'name' => 'Christmas Island',
              'status' => 0,
              'phonecode' => 61,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 46,
              'sortname' => 'CC',
              'name' => 'Cocos (Keeling) Islands',
              'status' => 0,
              'phonecode' => 672,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 47,
              'sortname' => 'CO',
              'name' => 'Colombia',
              'status' => 0,
              'phonecode' => 57,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 48,
              'sortname' => 'KM',
              'name' => 'Comoros',
              'status' => 0,
              'phonecode' => 269,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 49,
              'sortname' => 'CG',
              'name' => 'Republic Of The Congo',
              'status' => 0,
              'phonecode' => 242,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 50,
              'sortname' => 'CD',
              'name' => 'Democratic Republic Of The Congo',
              'status' => 0,
              'phonecode' => 242,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 51,
              'sortname' => 'CK',
              'name' => 'Cook Islands',
              'status' => 0,
              'phonecode' => 682,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 52,
              'sortname' => 'CR',
              'name' => 'Costa Rica',
              'status' => 0,
              'phonecode' => 506,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 53,
              'sortname' => 'CI',
              'name' => 'Cote D\'Ivoire (Ivory Coast)',
              'status' => 0,
              'phonecode' => 225,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 54,
              'sortname' => 'HR',
              'name' => 'Croatia (Hrvatska)',
              'status' => 0,
              'phonecode' => 385,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 55,
              'sortname' => 'CU',
              'name' => 'Cuba',
              'status' => 0,
              'phonecode' => 53,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 56,
              'sortname' => 'CY',
              'name' => 'Cyprus',
              'status' => 0,
              'phonecode' => 357,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 57,
              'sortname' => 'CZ',
              'name' => 'Czech Republic',
              'status' => 0,
              'phonecode' => 420,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 58,
              'sortname' => 'DK',
              'name' => 'Denmark',
              'status' => 0,
              'phonecode' => 45,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 59,
              'sortname' => 'DJ',
              'name' => 'Djibouti',
              'status' => 0,
              'phonecode' => 253,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 60,
              'sortname' => 'DM',
              'name' => 'Dominica',
              'status' => 0,
              'phonecode' => 1767,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 61,
              'sortname' => 'DO',
              'name' => 'Dominican Republic',
              'status' => 0,
              'phonecode' => 1809,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 62,
              'sortname' => 'TP',
              'name' => 'East Timor',
              'status' => 0,
              'phonecode' => 670,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 63,
              'sortname' => 'EC',
              'name' => 'Ecuador',
              'status' => 0,
              'phonecode' => 593,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 64,
              'sortname' => 'EG',
              'name' => 'Egypt',
              'status' => 0,
              'phonecode' => 20,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 65,
              'sortname' => 'SV',
              'name' => 'El Salvador',
              'status' => 0,
              'phonecode' => 503,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 66,
              'sortname' => 'GQ',
              'name' => 'Equatorial Guinea',
              'status' => 0,
              'phonecode' => 240,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 67,
              'sortname' => 'ER',
              'name' => 'Eritrea',
              'status' => 0,
              'phonecode' => 291,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 68,
              'sortname' => 'EE',
              'name' => 'Estonia',
              'status' => 0,
              'phonecode' => 372,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 69,
              'sortname' => 'ET',
              'name' => 'Ethiopia',
              'status' => 0,
              'phonecode' => 251,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 70,
              'sortname' => 'XA',
              'name' => 'External Territories of Australia',
              'status' => 0,
              'phonecode' => 61,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 71,
              'sortname' => 'FK',
              'name' => 'Falkland Islands',
              'status' => 0,
              'phonecode' => 500,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 72,
              'sortname' => 'FO',
              'name' => 'Faroe Islands',
              'status' => 0,
              'phonecode' => 298,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 73,
              'sortname' => 'FJ',
              'name' => 'Fiji Islands',
              'status' => 0,
              'phonecode' => 679,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 74,
              'sortname' => 'FI',
              'name' => 'Finland',
              'status' => 0,
              'phonecode' => 358,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 75,
              'sortname' => 'FR',
              'name' => 'France',
              'status' => 0,
              'phonecode' => 33,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 76,
              'sortname' => 'GF',
              'name' => 'French Guiana',
              'status' => 0,
              'phonecode' => 594,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 77,
              'sortname' => 'PF',
              'name' => 'French Polynesia',
              'status' => 0,
              'phonecode' => 689,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 78,
              'sortname' => 'TF',
              'name' => 'French Southern Territories',
              'status' => 0,
              'phonecode' => 0,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 79,
              'sortname' => 'GA',
              'name' => 'Gabon',
              'status' => 0,
              'phonecode' => 241,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 80,
              'sortname' => 'GM',
              'name' => 'Gambia The',
              'status' => 0,
              'phonecode' => 220,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 81,
              'sortname' => 'GE',
              'name' => 'Georgia',
              'status' => 0,
              'phonecode' => 995,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 82,
              'sortname' => 'DE',
              'name' => 'Germany',
              'status' => 0,
              'phonecode' => 49,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 83,
              'sortname' => 'GH',
              'name' => 'Ghana',
              'status' => 0,
              'phonecode' => 233,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 84,
              'sortname' => 'GI',
              'name' => 'Gibraltar',
              'status' => 0,
              'phonecode' => 350,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 85,
              'sortname' => 'GR',
              'name' => 'Greece',
              'status' => 0,
              'phonecode' => 30,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 86,
              'sortname' => 'GL',
              'name' => 'Greenland',
              'status' => 0,
              'phonecode' => 299,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 87,
              'sortname' => 'GD',
              'name' => 'Grenada',
              'status' => 0,
              'phonecode' => 1473,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 88,
              'sortname' => 'GP',
              'name' => 'Guadeloupe',
              'status' => 0,
              'phonecode' => 590,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 89,
              'sortname' => 'GU',
              'name' => 'Guam',
              'status' => 0,
              'phonecode' => 1671,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 90,
              'sortname' => 'GT',
              'name' => 'Guatemala',
              'status' => 0,
              'phonecode' => 502,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 91,
              'sortname' => 'XU',
              'name' => 'Guernsey and Alderney',
              'status' => 0,
              'phonecode' => 44,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 92,
              'sortname' => 'GN',
              'name' => 'Guinea',
              'status' => 0,
              'phonecode' => 224,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 93,
              'sortname' => 'GW',
              'name' => 'Guinea-Bissau',
              'status' => 0,
              'phonecode' => 245,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 94,
              'sortname' => 'GY',
              'name' => 'Guyana',
              'status' => 0,
              'phonecode' => 592,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 95,
              'sortname' => 'HT',
              'name' => 'Haiti',
              'status' => 0,
              'phonecode' => 509,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 96,
              'sortname' => 'HM',
              'name' => 'Heard and McDonald Islands',
              'status' => 0,
              'phonecode' => 0,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 97,
              'sortname' => 'HN',
              'name' => 'Honduras',
              'status' => 0,
              'phonecode' => 504,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 98,
              'sortname' => 'HK',
              'name' => 'Hong Kong S.A.R.',
              'status' => 0,
              'phonecode' => 852,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 99,
              'sortname' => 'HU',
              'name' => 'Hungary',
              'status' => 0,
              'phonecode' => 36,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 100,
              'sortname' => 'IS',
              'name' => 'Iceland',
              'status' => 0,
              'phonecode' => 354,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 101,
              'sortname' => 'IN',
              'name' => 'India',
              'status' => 0,
              'phonecode' => 91,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 102,
              'sortname' => 'ID',
              'name' => 'Indonesia',
              'status' => 0,
              'phonecode' => 62,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 103,
              'sortname' => 'IR',
              'name' => 'Iran',
              'status' => 0,
              'phonecode' => 98,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 104,
              'sortname' => 'IQ',
              'name' => 'Iraq',
              'status' => 0,
              'phonecode' => 964,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 105,
              'sortname' => 'IE',
              'name' => 'Ireland',
              'status' => 0,
              'phonecode' => 353,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 106,
              'sortname' => 'IL',
              'name' => 'Israel',
              'status' => 0,
              'phonecode' => 972,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 107,
              'sortname' => 'IT',
              'name' => 'Italy',
              'status' => 0,
              'phonecode' => 39,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 108,
              'sortname' => 'JM',
              'name' => 'Jamaica',
              'status' => 0,
              'phonecode' => 1876,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 109,
              'sortname' => 'JP',
              'name' => 'Japan',
              'status' => 0,
              'phonecode' => 81,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 110,
              'sortname' => 'XJ',
              'name' => 'Jersey',
              'status' => 0,
              'phonecode' => 44,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 111,
              'sortname' => 'JO',
              'name' => 'Jordan',
              'status' => 0,
              'phonecode' => 962,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 112,
              'sortname' => 'KZ',
              'name' => 'Kazakhstan',
              'status' => 0,
              'phonecode' => 7,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 113,
              'sortname' => 'KE',
              'name' => 'Kenya',
              'status' => 0,
              'phonecode' => 254,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 114,
              'sortname' => 'KI',
              'name' => 'Kiribati',
              'status' => 0,
              'phonecode' => 686,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 115,
              'sortname' => 'KP',
              'name' => 'Korea North',
              'status' => 0,
              'phonecode' => 850,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 116,
              'sortname' => 'KR',
              'name' => 'Korea South',
              'status' => 0,
              'phonecode' => 82,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 117,
              'sortname' => 'KW',
              'name' => 'Kuwait',
              'status' => 0,
              'phonecode' => 965,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 118,
              'sortname' => 'KG',
              'name' => 'Kyrgyzstan',
              'status' => 0,
              'phonecode' => 996,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 119,
              'sortname' => 'LA',
              'name' => 'Laos',
              'status' => 0,
              'phonecode' => 856,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 120,
              'sortname' => 'LV',
              'name' => 'Latvia',
              'status' => 0,
              'phonecode' => 371,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 121,
              'sortname' => 'LB',
              'name' => 'Lebanon',
              'status' => 0,
              'phonecode' => 961,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 122,
              'sortname' => 'LS',
              'name' => 'Lesotho',
              'status' => 0,
              'phonecode' => 266,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 123,
              'sortname' => 'LR',
              'name' => 'Liberia',
              'status' => 0,
              'phonecode' => 231,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 124,
              'sortname' => 'LY',
              'name' => 'Libya',
              'status' => 0,
              'phonecode' => 218,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 125,
              'sortname' => 'LI',
              'name' => 'Liechtenstein',
              'status' => 0,
              'phonecode' => 423,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 126,
              'sortname' => 'LT',
              'name' => 'Lithuania',
              'status' => 0,
              'phonecode' => 370,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 127,
              'sortname' => 'LU',
              'name' => 'Luxembourg',
              'status' => 0,
              'phonecode' => 352,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 128,
              'sortname' => 'MO',
              'name' => 'Macau S.A.R.',
              'status' => 0,
              'phonecode' => 853,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 129,
              'sortname' => 'MK',
              'name' => 'Macedonia',
              'status' => 0,
              'phonecode' => 389,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 130,
              'sortname' => 'MG',
              'name' => 'Madagascar',
              'status' => 0,
              'phonecode' => 261,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 131,
              'sortname' => 'MW',
              'name' => 'Malawi',
              'status' => 0,
              'phonecode' => 265,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 132,
              'sortname' => 'MY',
              'name' => 'Malaysia',
              'status' => 0,
              'phonecode' => 60,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 133,
              'sortname' => 'MV',
              'name' => 'Maldives',
              'status' => 0,
              'phonecode' => 960,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 134,
              'sortname' => 'ML',
              'name' => 'Mali',
              'status' => 0,
              'phonecode' => 223,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 135,
              'sortname' => 'MT',
              'name' => 'Malta',
              'status' => 0,
              'phonecode' => 356,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 136,
              'sortname' => 'XM',
              'name' => 'Man (Isle of)',
              'status' => 0,
              'phonecode' => 44,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 137,
              'sortname' => 'MH',
              'name' => 'Marshall Islands',
              'status' => 0,
              'phonecode' => 692,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 138,
              'sortname' => 'MQ',
              'name' => 'Martinique',
              'status' => 0,
              'phonecode' => 596,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 139,
              'sortname' => 'MR',
              'name' => 'Mauritania',
              'status' => 0,
              'phonecode' => 222,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 140,
              'sortname' => 'MU',
              'name' => 'Mauritius',
              'status' => 0,
              'phonecode' => 230,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 141,
              'sortname' => 'YT',
              'name' => 'Mayotte',
              'status' => 0,
              'phonecode' => 269,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 142,
              'sortname' => 'MX',
              'name' => 'Mexico',
              'status' => 0,
              'phonecode' => 52,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 143,
              'sortname' => 'FM',
              'name' => 'Micronesia',
              'status' => 0,
              'phonecode' => 691,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 144,
              'sortname' => 'MD',
              'name' => 'Moldova',
              'status' => 0,
              'phonecode' => 373,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 145,
              'sortname' => 'MC',
              'name' => 'Monaco',
              'status' => 0,
              'phonecode' => 377,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 146,
              'sortname' => 'MN',
              'name' => 'Mongolia',
              'status' => 0,
              'phonecode' => 976,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 147,
              'sortname' => 'MS',
              'name' => 'Montserrat',
              'status' => 0,
              'phonecode' => 1664,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 148,
              'sortname' => 'MA',
              'name' => 'Morocco',
              'status' => 0,
              'phonecode' => 212,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 149,
              'sortname' => 'MZ',
              'name' => 'Mozambique',
              'status' => 0,
              'phonecode' => 258,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 150,
              'sortname' => 'MM',
              'name' => 'Myanmar',
              'status' => 0,
              'phonecode' => 95,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 151,
              'sortname' => 'NA',
              'name' => 'Namibia',
              'status' => 0,
              'phonecode' => 264,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 152,
              'sortname' => 'NR',
              'name' => 'Nauru',
              'status' => 0,
              'phonecode' => 674,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 153,
              'sortname' => 'NP',
              'name' => 'Nepal',
              'status' => 0,
              'phonecode' => 977,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 154,
              'sortname' => 'AN',
              'name' => 'Netherlands Antilles',
              'status' => 0,
              'phonecode' => 599,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 155,
              'sortname' => 'NL',
              'name' => 'Netherlands The',
              'status' => 0,
              'phonecode' => 31,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 156,
              'sortname' => 'NC',
              'name' => 'New Caledonia',
              'status' => 0,
              'phonecode' => 687,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 157,
              'sortname' => 'NZ',
              'name' => 'New Zealand',
              'status' => 0,
              'phonecode' => 64,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 158,
              'sortname' => 'NI',
              'name' => 'Nicaragua',
              'status' => 0,
              'phonecode' => 505,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 159,
              'sortname' => 'NE',
              'name' => 'Niger',
              'status' => 0,
              'phonecode' => 227,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 160,
              'sortname' => 'NG',
              'name' => 'Nigeria',
              'status' => 0,
              'phonecode' => 234,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 161,
              'sortname' => 'NU',
              'name' => 'Niue',
              'status' => 0,
              'phonecode' => 683,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 162,
              'sortname' => 'NF',
              'name' => 'Norfolk Island',
              'status' => 0,
              'phonecode' => 672,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 163,
              'sortname' => 'MP',
              'name' => 'Northern Mariana Islands',
              'status' => 0,
              'phonecode' => 1670,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 164,
              'sortname' => 'NO',
              'name' => 'Norway',
              'status' => 0,
              'phonecode' => 47,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 165,
              'sortname' => 'OM',
              'name' => 'Oman',
              'status' => 0,
              'phonecode' => 968,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 166,
              'sortname' => 'PK',
              'name' => 'Pakistan',
              'status' => 0,
              'phonecode' => 92,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 167,
              'sortname' => 'PW',
              'name' => 'Palau',
              'status' => 0,
              'phonecode' => 680,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 168,
              'sortname' => 'PS',
              'name' => 'Palestinian Territory Occupied',
              'status' => 0,
              'phonecode' => 970,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 169,
              'sortname' => 'PA',
              'name' => 'Panama',
              'status' => 0,
              'phonecode' => 507,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 170,
              'sortname' => 'PG',
              'name' => 'Papua new Guinea',
              'status' => 0,
              'phonecode' => 675,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 171,
              'sortname' => 'PY',
              'name' => 'Paraguay',
              'status' => 0,
              'phonecode' => 595,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 172,
              'sortname' => 'PE',
              'name' => 'Peru',
              'status' => 0,
              'phonecode' => 51,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 173,
              'sortname' => 'PH',
              'name' => 'Philippines',
              'status' => 0,
              'phonecode' => 63,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 174,
              'sortname' => 'PN',
              'name' => 'Pitcairn Island',
              'status' => 0,
              'phonecode' => 0,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 175,
              'sortname' => 'PL',
              'name' => 'Poland',
              'status' => 0,
              'phonecode' => 48,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 176,
              'sortname' => 'PT',
              'name' => 'Portugal',
              'status' => 0,
              'phonecode' => 351,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 177,
              'sortname' => 'PR',
              'name' => 'Puerto Rico',
              'status' => 0,
              'phonecode' => 1787,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 178,
              'sortname' => 'QA',
              'name' => 'Qatar',
              'status' => 0,
              'phonecode' => 974,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 179,
              'sortname' => 'RE',
              'name' => 'Reunion',
              'status' => 0,
              'phonecode' => 262,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 180,
              'sortname' => 'RO',
              'name' => 'Romania',
              'status' => 0,
              'phonecode' => 40,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 181,
              'sortname' => 'RU',
              'name' => 'Russia',
              'status' => 0,
              'phonecode' => 70,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 182,
              'sortname' => 'RW',
              'name' => 'Rwanda',
              'status' => 0,
              'phonecode' => 250,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 183,
              'sortname' => 'SH',
              'name' => 'Saint Helena',
              'status' => 0,
              'phonecode' => 290,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 184,
              'sortname' => 'KN',
              'name' => 'Saint Kitts And Nevis',
              'status' => 0,
              'phonecode' => 1869,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 185,
              'sortname' => 'LC',
              'name' => 'Saint Lucia',
              'status' => 0,
              'phonecode' => 1758,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 186,
              'sortname' => 'PM',
              'name' => 'Saint Pierre and Miquelon',
              'status' => 0,
              'phonecode' => 508,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 187,
              'sortname' => 'VC',
              'name' => 'Saint Vincent And The Grenadines',
              'status' => 0,
              'phonecode' => 1784,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 188,
              'sortname' => 'WS',
              'name' => 'Samoa',
              'status' => 0,
              'phonecode' => 684,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 189,
              'sortname' => 'SM',
              'name' => 'San Marino',
              'status' => 0,
              'phonecode' => 378,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 190,
              'sortname' => 'ST',
              'name' => 'Sao Tome and Principe',
              'status' => 0,
              'phonecode' => 239,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 191,
              'sortname' => 'SA',
              'name' => 'Saudi Arabia',
              'status' => 0,
              'phonecode' => 966,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 192,
              'sortname' => 'SN',
              'name' => 'Senegal',
              'status' => 0,
              'phonecode' => 221,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 193,
              'sortname' => 'RS',
              'name' => 'Serbia',
              'status' => 0,
              'phonecode' => 381,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 194,
              'sortname' => 'SC',
              'name' => 'Seychelles',
              'status' => 0,
              'phonecode' => 248,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 195,
              'sortname' => 'SL',
              'name' => 'Sierra Leone',
              'status' => 0,
              'phonecode' => 232,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 196,
              'sortname' => 'SG',
              'name' => 'Singapore',
              'status' => 0,
              'phonecode' => 65,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 197,
              'sortname' => 'SK',
              'name' => 'Slovakia',
              'status' => 0,
              'phonecode' => 421,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 198,
              'sortname' => 'SI',
              'name' => 'Slovenia',
              'status' => 0,
              'phonecode' => 386,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 199,
              'sortname' => 'XG',
              'name' => 'Smaller Territories of the UK',
              'status' => 0,
              'phonecode' => 44,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 200,
              'sortname' => 'SB',
              'name' => 'Solomon Islands',
              'status' => 0,
              'phonecode' => 677,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 201,
              'sortname' => 'SO',
              'name' => 'Somalia',
              'status' => 0,
              'phonecode' => 252,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 202,
              'sortname' => 'ZA',
              'name' => 'South Africa',
              'status' => 0,
              'phonecode' => 27,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 203,
              'sortname' => 'GS',
              'name' => 'South Georgia',
              'status' => 0,
              'phonecode' => 0,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 204,
              'sortname' => 'SS',
              'name' => 'South Sudan',
              'status' => 0,
              'phonecode' => 211,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 205,
              'sortname' => 'ES',
              'name' => 'Spain',
              'status' => 0,
              'phonecode' => 34,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 206,
              'sortname' => 'LK',
              'name' => 'Sri Lanka',
              'status' => 0,
              'phonecode' => 94,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 207,
              'sortname' => 'SD',
              'name' => 'Sudan',
              'status' => 0,
              'phonecode' => 249,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 208,
              'sortname' => 'SR',
              'name' => 'Suriname',
              'status' => 0,
              'phonecode' => 597,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 209,
              'sortname' => 'SJ',
              'name' => 'Svalbard And Jan Mayen Islands',
              'status' => 0,
              'phonecode' => 47,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 210,
              'sortname' => 'SZ',
              'name' => 'Swaziland',
              'status' => 0,
              'phonecode' => 268,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 211,
              'sortname' => 'SE',
              'name' => 'Sweden',
              'status' => 0,
              'phonecode' => 46,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 212,
              'sortname' => 'CH',
              'name' => 'Switzerland',
              'status' => 0,
              'phonecode' => 41,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 213,
              'sortname' => 'SY',
              'name' => 'Syria',
              'status' => 0,
              'phonecode' => 963,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 214,
              'sortname' => 'TW',
              'name' => 'Taiwan',
              'status' => 0,
              'phonecode' => 886,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 215,
              'sortname' => 'TJ',
              'name' => 'Tajikistan',
              'status' => 0,
              'phonecode' => 992,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 216,
              'sortname' => 'TZ',
              'name' => 'Tanzania',
              'status' => 0,
              'phonecode' => 255,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 217,
              'sortname' => 'TH',
              'name' => 'Thailand',
              'status' => 0,
              'phonecode' => 66,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 218,
              'sortname' => 'TG',
              'name' => 'Togo',
              'status' => 0,
              'phonecode' => 228,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 219,
              'sortname' => 'TK',
              'name' => 'Tokelau',
              'status' => 0,
              'phonecode' => 690,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 220,
              'sortname' => 'TO',
              'name' => 'Tonga',
              'status' => 0,
              'phonecode' => 676,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 221,
              'sortname' => 'TT',
              'name' => 'Trinidad And Tobago',
              'status' => 0,
              'phonecode' => 1868,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 222,
              'sortname' => 'TN',
              'name' => 'Tunisia',
              'status' => 0,
              'phonecode' => 216,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 223,
              'sortname' => 'TR',
              'name' => 'Turkey',
              'status' => 0,
              'phonecode' => 90,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 224,
              'sortname' => 'TM',
              'name' => 'Turkmenistan',
              'status' => 0,
              'phonecode' => 7370,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 225,
              'sortname' => 'TC',
              'name' => 'Turks And Caicos Islands',
              'status' => 0,
              'phonecode' => 1649,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 226,
              'sortname' => 'TV',
              'name' => 'Tuvalu',
              'status' => 0,
              'phonecode' => 688,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 227,
              'sortname' => 'UG',
              'name' => 'Uganda',
              'status' => 0,
              'phonecode' => 256,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 228,
              'sortname' => 'UA',
              'name' => 'Ukraine',
              'status' => 0,
              'phonecode' => 380,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 229,
              'sortname' => 'AE',
              'name' => 'United Arab Emirates',
              'status' => 0,
              'phonecode' => 971,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 230,
              'sortname' => 'GB',
              'name' => 'United Kingdom',
              'status' => 0,
              'phonecode' => 44,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 231,
              'sortname' => 'US',
              'name' => 'United States',
              'status' => 0,
              'phonecode' => 1,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 232,
              'sortname' => 'UM',
              'name' => 'United States Minor Outlying Islands',
              'status' => 0,
              'phonecode' => 1,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 233,
              'sortname' => 'UY',
              'name' => 'Uruguay',
              'status' => 0,
              'phonecode' => 598,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 234,
              'sortname' => 'UZ',
              'name' => 'Uzbekistan',
              'status' => 0,
              'phonecode' => 998,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 235,
              'sortname' => 'VU',
              'name' => 'Vanuatu',
              'status' => 0,
              'phonecode' => 678,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 236,
              'sortname' => 'VA',
              'name' => 'Vatican City State (Holy See)',
              'status' => 0,
              'phonecode' => 39,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 237,
              'sortname' => 'VE',
              'name' => 'Venezuela',
              'status' => 0,
              'phonecode' => 58,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 238,
              'sortname' => 'VN',
              'name' => 'Vietnam',
              'status' => 0,
              'phonecode' => 84,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 239,
              'sortname' => 'VG',
              'name' => 'Virgin Islands (British)',
              'status' => 0,
              'phonecode' => 1284,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 240,
              'sortname' => 'VI',
              'name' => 'Virgin Islands (US)',
              'status' => 0,
              'phonecode' => 1340,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 241,
              'sortname' => 'WF',
              'name' => 'Wallis And Futuna Islands',
              'status' => 0,
              'phonecode' => 681,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 242,
              'sortname' => 'EH',
              'name' => 'Western Sahara',
              'status' => 0,
              'phonecode' => 212,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 243,
              'sortname' => 'YE',
              'name' => 'Yemen',
              'status' => 0,
              'phonecode' => 967,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 244,
              'sortname' => 'YU',
              'name' => 'Yugoslavia',
              'status' => 0,
              'phonecode' => 38,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 245,
              'sortname' => 'ZM',
              'name' => 'Zambia',
              'status' => 0,
              'phonecode' => 260,
              'added_by' => 1,
              'updated_by' => 1
            ),
            array(
              'id' => 246,
              'sortname' => 'ZW',
              'name' => 'Zimbabwe',
              'status' => 0,
              'phonecode' => 263,
              'added_by' => 1,
              'updated_by' => 1
            )
          );

        DB::table('countries')->insert($data);
    }
}
