<?php

namespace Database\Seeders;

use App\Models\GuestLayoutManagment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GuestLayoutManagmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contents = [
            [
                "name" => "Home Content 1",
                "content" => '   <div class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8 mt-8">
    <div class="grid md:grid-cols-2 gap-4 md:gap-8 xl:gap-20 md:items-center">
      <div>
        <h1 class="block text-3xl font-bold text-gray-800 sm:text-4xl lg:text-6xl lg:leading-tight ">Welcome to<span class="text-blue-600"> Walcot A.B.C, </span> where it’s good to belong </h1>
        <p class="mt-3 text-lg text-gray-800 dark:text-neutral-400">Walcot A.B.C. is the oldest boxing club in Swindon. We are an England Boxing affiliated club, with many years of history within Swindon and the Western Counties..</p>
  
  
      </div>
  
      <div class="relative ms-4">
        <img class="w-full rounded-md" src="https://images.squarespace-cdn.com/content/v1/649f381a84d74a5fb2b82ce7/a1cd5cd4-a649-455c-a9ce-9f62394f0f91/Jake+Smith+0.jpg?format=500w" alt="Hero Image">
        <div class="absolute inset-0 -z-[1] bg-gradient-to-tr from-gray-200 via-white/0 to-white/0 size-full rounded-md mt-4 -mb-4 me-4 -ms-4 lg:mt-6 lg:-mb-6 lg:me-6 lg:-ms-6 dark:from-neutral-800 dark:via-neutral-900/0 dark:to-neutral-900/0"></div>
  
        <div class="absolute bottom-0 start-0">
          <svg class="w-2/3 ms-auto h-auto text-white dark:text-neutral-900" width="630" height="451" viewBox="0 0 630 451" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="531" y="352" width="99" height="99" fill="currentColor"/>
            <rect x="140" y="352" width="106" height="99" fill="currentColor"/>
            <rect x="482" y="402" width="64" height="49" fill="currentColor"/>
            <rect x="433" y="402" width="63" height="49" fill="currentColor"/>
            <rect x="384" y="352" width="49" height="50" fill="currentColor"/>
            <rect x="531" y="328" width="50" height="50" fill="currentColor"/>
            <rect x="99" y="303" width="49" height="58" fill="currentColor"/>
            <rect x="99" y="352" width="49" height="50" fill="currentColor"/>
            <rect x="99" y="392" width="49" height="59" fill="currentColor"/>
            <rect x="44" y="402" width="66" height="49" fill="currentColor"/>
            <rect x="234" y="402" width="62" height="49" fill="currentColor"/>
            <rect x="334" y="303" width="50" height="49" fill="currentColor"/>
            <rect x="581" width="49" height="49" fill="currentColor"/>
            <rect x="581" width="49" height="64" fill="currentColor"/>
            <rect x="482" y="123" width="49" height="49" fill="currentColor"/>
            <rect x="507" y="124" width="49" height="24" fill="currentColor"/>
            <rect x="531" y="49" width="99" height="99" fill="currentColor"/>
          </svg>
        </div>
      </div>
    </div>
  </div>',
                "fk_menu_id" => 1,
                "sort_order" => 1,
                "is_active" => 1
            ],
            [
                "name" => "Home Content 2",
                "content" => '<div class="bg-neutral-900">
        <div class="max-w-5xl mx-auto px-4 xl:px-0 pt-24 lg:pt-32 pb-24">
            <h1 class="font-semibold text-white text-5xl md:text-6xl">
                <span class="text-[#ff0] ">Walcot A.B.C</span> has trained many champion boxers over the years
            </h1>
            <div class="max-w-4xl">
                <p class="mt-5 text-neutral-400 text-lg">
                    Such as Jamie Cox, Marlon Reid, Danny Bharj, Ryan Martin, Said Eltuyev, Connor Wells, Jorgen Cetaj,
                    Patrick McDonagh, Tommy McDonagh and many more. All have made history for Swindon and the Western
                    Counties throughout the years due to their determination, hard work and the coaching they had
                    received from Walcot A.B.C. </p>

                <p class="mt-5 text-neutral-400 text-lg">
                    We are not just a regular gym; We are a club and we strive to teach our members respect, discipline,
                    and the value of hard work.</p>
            </div>
        </div>
    </div>
    <div class="relative overflow-hidden pt-4 bg-neutral-900">
        <svg class="absolute -bottom-20 start-1/2 w-[1900px] transform -translate-x-1/2" width="2745" height="488"
            viewBox="0 0 2745 488" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M0.5 330.864C232.505 403.801 853.749 527.683 1482.69 439.719C2111.63 351.756 2585.54 434.588 2743.87 487"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 308.873C232.505 381.81 853.749 505.692 1482.69 417.728C2111.63 329.765 2585.54 412.597 2743.87 465.009"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 286.882C232.505 359.819 853.749 483.701 1482.69 395.738C2111.63 307.774 2585.54 390.606 2743.87 443.018"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 264.891C232.505 337.828 853.749 461.71 1482.69 373.747C2111.63 285.783 2585.54 368.615 2743.87 421.027"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 242.9C232.505 315.837 853.749 439.719 1482.69 351.756C2111.63 263.792 2585.54 346.624 2743.87 399.036"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 220.909C232.505 293.846 853.749 417.728 1482.69 329.765C2111.63 241.801 2585.54 324.633 2743.87 377.045"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 198.918C232.505 271.855 853.749 395.737 1482.69 307.774C2111.63 219.81 2585.54 302.642 2743.87 355.054"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 176.927C232.505 249.864 853.749 373.746 1482.69 285.783C2111.63 197.819 2585.54 280.651 2743.87 333.063"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 154.937C232.505 227.873 853.749 351.756 1482.69 263.792C2111.63 175.828 2585.54 258.661 2743.87 311.072"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 132.946C232.505 205.882 853.749 329.765 1482.69 241.801C2111.63 153.837 2585.54 236.67 2743.87 289.082"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 110.955C232.505 183.891 853.749 307.774 1482.69 219.81C2111.63 131.846 2585.54 214.679 2743.87 267.091"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 88.9639C232.505 161.901 853.749 285.783 1482.69 197.819C2111.63 109.855 2585.54 192.688 2743.87 245.1"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 66.9729C232.505 139.91 853.749 263.792 1482.69 175.828C2111.63 87.8643 2585.54 170.697 2743.87 223.109"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 44.9819C232.505 117.919 853.749 241.801 1482.69 153.837C2111.63 65.8733 2585.54 148.706 2743.87 201.118"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 22.991C232.505 95.9276 853.749 219.81 1482.69 131.846C2111.63 43.8824 2585.54 126.715 2743.87 179.127"
                class="stroke-neutral-700/50" stroke="currentColor" />
            <path
                d="M0.5 1C232.505 73.9367 853.749 197.819 1482.69 109.855C2111.63 21.8914 2585.54 104.724 2743.87 157.136"
                class="stroke-neutral-700/50" stroke="currentColor" />
        </svg>

        <div class="relative z-10">
            <div class="max-w-5xl px-4 xl:px-0 mx-auto">
                <div class="mb-4">
                    <h2 class="text-neutral-400">We welcome individuals of all levels, whether you’re a complete novice,
                        a seasoned boxer, or somebody that wants to train recreationally. There is a place for you at
                        Walcot.</h2>
                </div>

            </div>
        </div>
    </div>',
                "fk_menu_id" => 1,
                "sort_order" => 2,
                "is_active" => 1
            ],
        ];

        foreach ($contents as $content) {
            GuestLayoutManagment::create($content);
            sleep(1);
        }
    }
}
