<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    die("Stop");

    \Illuminate\Support\Facades\DB::table('countries')->truncate();
    \Illuminate\Support\Facades\DB::table('regions')->truncate();
    \Illuminate\Support\Facades\DB::table('cities')->truncate();

    $vk = new VK\VK('5600549', 'bOVOKziTAUzyYrZXEJ6p');
    $vk->setApiVersion('5.53');



    $countries = $vk->api('database.getCountries', [
        'need_all'  => true,
        'count'     => 1000
    ])['response'];

    if ($countries['count']) {
        \Illuminate\Support\Facades\DB::table('countries')->insert($countries['items']);
        foreach ($countries['items'] as $country) {

            $offset = 0;

            do {

                $regions_response = $vk->api('database.getRegions', [
                    'count'         => 1000,
                    'country_id'    => $country['id'],
                    'offset'        => $offset
                ]);

                $offset += 1000;

                if (isset($regions_response['error'])) {
                    dd($regions_response);
                }

                $regions = $regions_response['response'];

                if (count($regions['items'])) {
                    foreach ($regions['items'] as $region) {
                        \Illuminate\Support\Facades\DB::table('regions')->insert(['id' => $region['id'], 'country_id' => $country['id'], 'title' => $region['title']]);
                    }
                }

            }while(count($regions['items']) == 1000);

            $offset3 = 0;

            do {

                $cities_response = $vk->api('database.getCities', [
                    'need_all'      => 1,
                    'count'         => 1000,
                    'country_id'    => $country['id'],
                    'offset'        => $offset3
                ]);

                $offset3 += 1000;

                if (isset($cities_response['error'])) {
                    dd($cities_response);
                }

                $cities = $cities_response['response'];

                if (count($cities['items'])) {
                    // Запись городов
                    foreach ($cities['items'] as $city) {
                        $ar = ['id' => $city['id'], 'country_id' => $country['id'], 'title' => $city['title']];
                        if (isset($city['region'])) {
                            $ar['region']    = $city['region'];
                        }
                        \Illuminate\Support\Facades\DB::table('cities')->insert($ar);
                    }

//                    // Выборка институтов



                }

            } while (count($cities['items']) == 1000);

        }
    }

});

Route::get('write', function(){

    \Illuminate\Support\Facades\DB::table('universities')->truncate();

    $vk = new VK\VK('5600549', 'bOVOKziTAUzyYrZXEJ6p');
    $vk->setApiVersion('5.53');

    $cities = \Illuminate\Support\Facades\DB::table('cities')->orderBy('country_id')->get();

    $count_re_request_str = '';

    foreach ($cities as $city) {

        $country = \Illuminate\Support\Facades\DB::table('countries')->where('id', $city->country_id)->orderBy('id')->first();

        $offset2 = 0;

        do {

            $count_re_request = 0;

            do {
                $count_re_request++;
                $universities_response = $vk->api('database.getUniversities', [
                    'need_all'      => 1,
                    'count'         => 1000,
                    'country_id'    => $country->id,
                    'city_id'       => $city->id,
                    'offset'        => $offset2
                ]);

                if ($count_re_request > 1) {
                    $count_re_request_str .= $count_re_request.'('.$city->id.'),';
                }

                if ( $count_re_request == 50) {
                    echo "Country = ".$country->id;
                    echo " City = ".$city->id;
                    die();
                }

            } while (isset($universities_response['error']['error_code']) && $universities_response['error']['error_code'] == 10);

            

            $offset2 += 1000;

            if (isset($universities_response['error'])) {
                dd($universities_response);
            }

            $universities = $universities_response['response'];

            if (count($universities['items'])) {
                // Запись институтов
                foreach ($universities['items'] as $university) {
                    $ar = [
                        'id'            => $university['id'],
                        'country_id'    => $country->id,
                        'city_id'       => $city->id,
                        'title'         => $university['title']
                    ];
                    \Illuminate\Support\Facades\DB::table('universities')->insert($ar);
                }
            }
        } while (count($universities['items']) == 1000);

    }

    echo "End<br>";
    echo $count_re_request_str;

});
