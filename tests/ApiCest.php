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
    public function tryApi(ApiTester $I)
    {
        $I->sendGet('/');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    /**
     * Увеличение просмотров
     *
     * @param \ApiTester $I
     */
    public function incViews(ApiTester $I)
    {
        $I->sendPost(
            '/project/entity/' . $this->randomId . '/',
            ['nb_views' => 1, 'nb_phone_views' => 1, 'return_counters' => 1]
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'data' => ['nb_views' => 1, 'nb_phone_views' => 1],
        ]);

        $I->sendPost('/project/entity/' . $this->randomId . '/', ['nb_views' => 1, 'return_counters' => 1]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'data' => ['nb_views' => 2, 'nb_phone_views' => 1],
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
            $this->randomId => ['nb_views' => 2, 'nb_phone_views' => 1],
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
            $this->randomId => ['nb_views' => 2, 'nb_phone_views' => 1],
        ]);
    }

    /**
     * Увеличение просмотров у другой сущности другого проекта
     *
     * @param \ApiTester $I
     */
    public function incViewsAnotherEntity(ApiTester $I)
    {
        $I->sendPost(
            '/project2/entity2/' . $this->randomId . '/',
            ['nb_views' => 1, 'nb_phone_views' => 1, 'return_counters' => 1]
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'data' => ['nb_views' => 1, 'nb_phone_views' => 1],
        ]);

        $I->sendPost(
            '/project2/entity2/' . $this->randomId . '/',
            ['nb_views' => 1, 'nb_phone_views' => 1, 'return_counters' => 1]
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'data' => ['nb_views' => 2, 'nb_phone_views' => 2],
        ]);
    }
}
