<?php

namespace tests;

use ApiTester;

/**
 * Тесты
 */
class ApiCest
{
    /**
     * Рандомный идентификатор с которым будут работать все тесты
     *
     * @var int
     */
    protected $randomId;

    /**
     * Выполняется перед каждым тестом
     */
    public function _before()
    {
        if (!$this->randomId) {
            $this->randomId = mt_rand(10000000, 90000000);
        }
    }

    /**
     * Проверка работоспособности сервиса
     *
     * @param \ApiTester $I
     */
//    public function tryApi(ApiTester $I)
//    {
//        $I->sendGet('/');
//        $I->seeResponseCodeIs(200);
//        $I->seeResponseIsJson();
//    }

    /**
     * Увеличение просмотров
     *
     * @param \ApiTester $I
     */
    public function incViews(ApiTester $I)
    {
        // Case: 0
        $I->sendPost(
            '/project/entity/' . $this->randomId . '/',
            json_encode([
                'data' => [
                    'page_views' => 1,
                    'phone_views' => 1,
                ],
            ])
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'data' => [
                'page_views' => 1,
                'phone_views' => 1],
        ]);

        // Case: 1
        $I->sendPost(
            '/project/entity/' . $this->randomId . '/',
            json_encode([
                'data' => [
                    'page_views' => 1,
                    'phone_views' => 1,
                ],
            ])
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'data' => [
                'page_views' => 1,
                'phone_views' => 1],
        ]);
        // Case:2
        $I->sendPost(
            '/project/entity/' . $this->randomId . '/',
            json_encode([
                'data' => [
                    'page_views' => 1,
                    'phone_views' => 0,
                ],
            ])
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'data' => [
                'page_views' => 2,
                'phone_views' => 1],
        ]);

        // Case: 3 нет phone_views
        $I->sendPost(
            '/project/entity/' . $this->randomId . '/',
            json_encode([
                'data' => [
                    'page_views' => 2
                ],
            ])
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'data' => [
                'page_views' => 4
            ],
        ]);

        // Case:4 нет page_views
        $I->sendPost(
            '/project/entity/' . $this->randomId . '/',
            json_encode([
                'data' => [
                    'phone_views' => 1,
                ],
            ])
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'data' => [
                'page_views' => 4,
                'phone_views' => 2],
        ]);

        //_______________________________ ROUTING HANDLE:

        // Case: 0 - Корректные параметры маршрута
        $I->sendPost(
            '/project/entity/' . $this->randomId . '/',
            json_encode([
                'data' => [
                    'page_views' => 1,
                    'phone_views' => 1,
                ],
            ])
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'data' => [
                'page_views' => 1,
                'phone_views' => 1
            ],
        ]);

        // Case: 1 - Некорректный параметр project
        $I->sendPost(
            '/invalid!project/entity/' . $this->randomId . '/',
            json_encode([
                'data' => [
                    'page_views' => 1,
                    'phone_views' => 1,
                ],
            ])
        );
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'error' => 'Invalid route parameters'
        ]);

        // Case: 2 - Некорректный параметр entity
        $I->sendPost(
            '/project/invalid!entity/' . $this->randomId . '/',
            json_encode([
                'data' => [
                    'page_views' => 1,
                    'phone_views' => 1,
                ],
            ])
        );
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'error' => 'Invalid route parameters'
        ]);

        // Case: 3 - Некорректный параметр id (не числовое значение)
        $I->sendPost(
            '/project/entity/invalid-id/',
            json_encode([
                'data' => [
                    'page_views' => 1,
                    'phone_views' => 1,
                ],
            ])
        );
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'error' => 'Invalid route parameters'
        ]);


    }


    /**
     * Получение просмотров
     *
     * @param \ApiTester $I
     */
    public function getViews(ApiTester $I)
    {
        $I->sendGet('/project/entity/' . $this->randomId . '/');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
                'data' => [
                    $this->randomId => [
                        'page_views' => 4,
                        'phone_views' => 2,
                    ],
                ],
        ]);
    }


    /**
     * Получение статистики
     *
     * @param \ApiTester $I
     */
    public function getStatistics(ApiTester $I)
    {
        // наш сервис иногда не может мгновенно выдать статистику ;-)
        sleep(1);

        $periods = [
            'last-year' => [
                'from' => date('2020-01-01'),
                'to'   => date('Y-m-d'),
            ],
        ];

        $I->sendGet('/project/entity/' . $this->randomId . '/periods/', ['period' => $periods]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
                'data' => [
                    'last-year' => [
                        $this->randomId =>[
                            'page_views' => 4,
                            'phone_views' => 2
                        ],
                    ],
                ],
        ]);
    }

// No NEEEEED ---------------->

//    /**
//     * Увеличение просмотров у другой сущности другого проекта
//     *
//     * @param \ApiTester $I
//     */
//    public function incViewsAnotherEntity(ApiTester $I)
//    {
//        $I->sendPost(
//            '/project2/entity2/' . $this->randomId . '/',
//            ['page_views' => 1, 'phone_views' => 1, 'return_counters' => 1]
//        );
//        $I->seeResponseCodeIs(200);
//        $I->seeResponseIsJson();
//
//        $I->seeResponseContainsJson([
//            'data' => ['page_views' => 1, 'phone_views' => 1],
//        ]);
//
//        $I->sendPost(
//            '/project2/entity2/' . $this->randomId . '/',
//            ['page_views' => 1, 'phone_views' => 1, 'return_counters' => 1]
//        );
//        $I->seeResponseCodeIs(200);
//        $I->seeResponseIsJson();
//
//        $I->seeResponseContainsJson([
//            'data' => ['page_views' => 2, 'phone_views' => 2],
//        ]);
//    }
}
