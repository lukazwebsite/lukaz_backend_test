<?php

namespace Database\Seeders;

use App\Models\Admin\Branch;
use Illuminate\Database\Seeder;

class OutletDetailSeeder extends Seeder
{
    /**
     * Move the outlet details that used to live in the storefront component
     * into the branches table so the section becomes fully dynamic.
     */
    public function run(): void
    {
        $outlets = [
            'rajshahi' => [
                'name_bn'       => 'রাজশাহী আউটলেট',
                'manager_phone' => '01798091972',
                'address'       => 'Kadirganj, Chal Potti, Beside Laz Pharma, Rajshahi.',
                'address_bn'    => 'কাদিরগঞ্জ, চাল পট্টি, লাজ ফার্মার পাশে, রাজশাহী।',
                'map_link'      => 'https://maps.app.goo.gl/3WmrsRVco7pczPw77?g_st=aw',
                'map_embed'     => 'https://www.google.com/maps?q=LUKAZ+Kadirganj+Rajshahi+Bangladesh&output=embed',
            ],
            'dhaka' => [
                'name_bn'       => 'ঢাকা আউটলেট',
                'manager_phone' => '01794034665',
                'address'       => 'Uttara Azampur Sector-3 (Main Road Adjacent), London Plaza, 3rd Floor.',
                'address_bn'    => 'উত্তরা আজমপুর সেক্টর-৩ (মেইন রোড সংলগ্ন) লন্ডন প্লাজা, তৃতীয় তলা।',
                'map_link'      => 'https://maps.app.goo.gl/t7UfLom9xgwN168g7?g_st=aw',
                'map_embed'     => 'https://www.google.com/maps?q=LUKAZ+London+Plaza+Azampur+Sector+3+Uttara+Dhaka+Bangladesh&output=embed',
            ],
            'sylhet' => [
                'name_bn'       => 'সিলেট আউটলেট',
                'manager_phone' => '01797273380',
                'address'       => 'Haat Super Mall, 2nd Floor, Uposhor, Sylhet.',
                'address_bn'    => 'হাট সুপার মল, দ্বিতীয় তলা, উপশহর, সিলেট।',
                'map_link'      => 'https://maps.app.goo.gl/1AuBeBVAJUNs9ctU9?g_st=aw',
                'map_embed'     => 'https://www.google.com/maps?q=LUKAZ+Super+Mall+Uposhor+Sylhet+Bangladesh&output=embed',
            ],
        ];

        foreach ($outlets as $keyword => $values) {
            $branch = Branch::where('slug', 'like', '%'.$keyword.'%')
                ->orWhere('name', 'like', '%'.$keyword.'%')
                ->first();

            if (!$branch) {
                $this->command->warn('No branch matched "'.$keyword.'", skipped.');
                continue;
            }

            // Only fill columns that are still empty, so admin edits are kept.
            $update = [];
            foreach ($values as $column => $value) {
                if (empty($branch->{$column})) {
                    $update[$column] = $value;
                }
            }

            if ($update) {
                $branch->update($update);
                $this->command->info('Updated branch "'.$branch->name.'".');
            }
        }
    }
}
