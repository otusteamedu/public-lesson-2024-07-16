# Полнотекстовый поиск в Symfony на примере ElasticSearch

## Готовим проект

1. Запускаем контейнеры командой `docker-compose up -d`
2. Входим в контейнер командой `docker exec -it php sh`. Дальнейшие команды будем выполнять из контейнера
3. Устанавливаем зависимости командой `composer install`
4. Выполняем миграции командой `php bin/console doctrine:migrations:migrate`

## Проверяем работоспособность приложения

1. Выполняем запрос Create good из Postman-коллекции, видим идентификатор созданной сущности
2. Выполняем запрос Get good из Postman-коллекции, видим данные сущности
3. Выполняем запрос Update good из Postman-коллекции, видим успешный ответ и изменённые данные в БД
4. Выполняем запрос Delete good из Postman-коллекции, видим успешный овети и то, что сущность удалена из БД

## Устанавливаем и настраиваем elastica-bundle

1. Добавляем сервис `elasticsearch` в `docker-compose.yml`
    ```yaml
    elasticsearch:
        image: docker.elastic.co/elasticsearch/elasticsearch:7.9.2
        container_name: 'elasticsearch'
        environment:
          - cluster.name=docker-cluster
          - bootstrap.memory_lock=true
          - discovery.type=single-node
          - "ES_JAVA_OPTS=-Xms512m -Xmx512m"
        ulimits:
          memlock:
            soft: -1
            hard: -1
        ports:
          - 9200:9200
          - 9300:9300
    ```
2. Перезапускаем контейнеры командами
    ```shell
    docker-compose stop
    docker-compose up -d
    ```
3. Устанавливаем пакет `friendsofsymfony/elastica-bundle`
4. В файле `.env` исправляем DSN для ElasticSearch
    ```shell script
    ELASTICSEARCH_URL=http://elasticsearch:9200/
    ```
5. В файле `config/packages/fos_elastica.yaml`
   1. Включаем сериализацию
       ```yaml
       serializer: ~
       ```
   2. в секции `indexes` удаляем `app` и добавляем секцию `good`:
       ```yaml
       good:
           persistence:
               driver: orm
               model: App\Entity\Good
       ```
6. Выполняем запрос Create good из Postman-коллекции
7. Выполняем запрос Elasticsearch Simple Search из Postman-коллекции, видим результат

## Добавляем поисковый запрос в API

1. Добавляем класс `App\Controller\Api\v1\FindGoods\Input\FindGoodsRequest`
    ```php
   <?php
    
    namespace App\Controller\Api\v1\FindGoods\Input;
    
    use Symfony\Component\DependencyInjection\Attribute\Exclude;
    use Symfony\Component\Validator\Constraints as Assert;
    
    #[Exclude]
    class FindGoodsRequest
    {
        public function __construct(
            #[Assert\NotBlank]
            public readonly string $search,
        ) {
        }
    } 
    ```
2. Добавляем класс `App\Controller\Api\v1\FindGood\Controller`
    ```php
    <?php
    
    namespace App\Controller\Api\v1\FindGoods;
    
    use App\Controller\Api\v1\FindGoods\Input\FindGoodsRequest;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\AsController;
    use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\SerializerInterface;
    
    #[AsController]
    class Controller
    {
        public function __construct(
            private readonly Manager $manager,
            private readonly SerializerInterface $serializer,
        ) {
        }
    
        #[Route(path: '/api/v1/find-goods', methods: ['POST'])]
        public function __invoke(#[MapRequestPayload] FindGoodsRequest $request): Response
        {
            $result = $this->manager->findGoods($request);
    
            return new JsonResponse($this->serializer->serialize(['goods' => $result], JsonEncoder::FORMAT), Response::HTTP_OK, [], true);
        }
    }
    ```
3. Добавляем класс `App\Controller\Api\v1\FindGoods\Manager`
    ```php
    <?php
    
    namespace App\Controller\Api\v1\FindGoods;
    
    use App\Controller\Api\v1\FindGoods\Input\FindGoodsRequest;
    use App\Entity\Good;
    use FOS\ElasticaBundle\Finder\FinderInterface;
    
    class Manager
    {
        public function __construct(private readonly FinderInterface $finder)
        {
        }
    
        /**
         * @return Good[]
         */
        public function findGoods(FindGoodsRequest $request): array
        {
            return $this->finder->find($request->search);
        }
    }
    ```
4. В файле `config/services.yaml` добавляем описание нового сервиса
    ```yaml
    App\Controller\Api\v1\FindGoods\Manager:
        arguments:
            $finder: '@fos_elastica.finder.good'
    ```
5. Выполняем запрос Find goods v1 из Postman-коллекции с точным соответствием слова и получаем результат.
6. Выполняем запрос Find goods v1 из Postman-коллекции с опечаткой и результат не получаем.

## Добавляем нечёткий поиск

1. Выполняем запрос Elasticsearch Fuzzy Search из Postman-коллекции с опечаткой, видим результат
2. В классе `App\Controller\Api\v1\FindGoods\Manager` исправляем метод `findGoods`
    ```php
    /**
     * @return Good[]
     */
    public function findGoods(FindGoodsRequest $request): array
    {
        return $this->finder->find($request->search.'~2');
    }
    ```
3. Выполняем запрос Find goods v2 из Postman-коллекции с опечаткой и получаем результат, но без весов.

## Добавляем метаданные в выдачу

1. Добавляем класс `App\Controller\Api\v1\FindGoods\Output\GoodResult`
    ```php
    <?php
    
    namespace App\Controller\Api\v1\FindGoods\Output;
    
    use App\Entity\Good;
    
    class GoodResult
    {
        public function __construct(
            public readonly Good $good,
            public readonly float $score,
        )
        {
        }
    }
    ```
2. Исправляем класс `App\Controller\Api\v1\FindGoods\Manager`
    ```php
    <?php
    
    namespace App\Controller\Api\v1\FindGoods;
    
    use App\Controller\Api\v1\FindGoods\Input\FindGoodsRequest;
    use App\Controller\Api\v1\FindGoods\Output\GoodResult;
    use FOS\ElasticaBundle\Finder\HybridFinderInterface;
    use FOS\ElasticaBundle\HybridResult;
    
    class Manager
    {
        public function __construct(private readonly HybridFinderInterface $finder)
        {
        }
    
        /**
         * @return GoodResult[]
         */
        public function findGoods(FindGoodsRequest $request): array
        {
            return array_map(
                static fn (HybridResult $result): GoodResult => new GoodResult(
                    $result->getTransformed(),
                    $result->getResult()->getScore(),
                ),
                $this->finder->findHybrid($request->search.'~2')
            );
        }
    }
    ```
3. Выполняем запрос Find goods v2 из Postman-коллекции с опечаткой и получаем результат с весами.

## Добавляем фильтрацию по логическому полю

1. Исправляем класс `App\Controller\Api\v1\FindGoods\Input\FindGoodsRequest`
    ```php
    <?php
    
    namespace App\Controller\Api\v1\FindGoods\Input;
    
    use Symfony\Component\DependencyInjection\Attribute\Exclude;
    use Symfony\Component\Validator\Constraints as Assert;
    
    #[Exclude]
    class FindGoodsRequest
    {
        public function __construct(
            #[Assert\NotBlank]
            public readonly string $search,
            public readonly bool $activeOnly,
        ) {
        }
    }
    ```
2. Исправляем класс `App\Controller\Api\v1\FindGoods\Manager` 
    ```php
    <?php
    
    namespace App\Controller\Api\v1\FindGoods;
    
    use App\Controller\Api\v1\FindGoods\Input\FindGoodsRequest;
    use App\Controller\Api\v1\FindGoods\Output\GoodResult;
    use Elastica\Query;
    use FOS\ElasticaBundle\Finder\HybridFinderInterface;
    use FOS\ElasticaBundle\HybridResult;
    
    class Manager
    {
        public function __construct(private readonly HybridFinderInterface $finder)
        {
        }
    
        /**
         * @return GoodResult[]
         */
        public function findGoods(FindGoodsRequest $request): array
        {
            $boolQuery = new Query\BoolQuery();
            if ($request->activeOnly) {
                $boolQuery->addMust(new Query\Term(['active' => true]));
            }
            $boolQuery->addShould(new Query\Fuzzy('name', $request->search));
            $boolQuery->addShould(new Query\Fuzzy('description', $request->search));
            return array_map(
                static fn (HybridResult $result): GoodResult => new GoodResult(
                    $result->getTransformed(),
                    $result->getResult()->getScore(),
                ),
                $this->finder->findHybrid(new Query($boolQuery))
            );
        }
    }
    ```
3. Выполняем запрос Update good из Postman-коллекции, чтобы деактивировать товар
4. Выполняем запрос Find goods v3 из Postman-коллекции и получаем результат с фильтрацией

## Добавляем фильтрацию по числовому полю

1. Исправляем класс `App\Controller\Api\v1\FindGoods\Input\FindGoodsRequest`
    ```php
    <?php
    
    namespace App\Controller\Api\v1\FindGoods\Input;
    
    use Symfony\Component\DependencyInjection\Attribute\Exclude;
    use Symfony\Component\Validator\Constraints as Assert;
    
    #[Exclude]
    class FindGoodsRequest
    {
        public function __construct(
            #[Assert\NotBlank]
            public readonly string $search,
            public readonly bool $activeOnly,
            #[Assert\Type('integer')]
            #[Assert\PositiveOrZero]
            public readonly ?int $minPrice = null,
            #[Assert\Type('integer')]
            #[Assert\PositiveOrZero]
            public readonly ?int $maxPrice = null,
        ) {
        }
    }
 
    ```
2. Исправляем класс `App\Controller\Api\v1\FindGoods\Manager`
    ```php
    <?php
    
    namespace App\Controller\Api\v1\FindGoods;
    
    use App\Controller\Api\v1\FindGoods\Input\FindGoodsRequest;
    use App\Controller\Api\v1\FindGoods\Output\GoodResult;
    use Elastica\Query;
    use FOS\ElasticaBundle\Finder\HybridFinderInterface;
    use FOS\ElasticaBundle\HybridResult;
    
    class Manager
    {
        public function __construct(private readonly HybridFinderInterface $finder)
        {
        }
    
        /**
         * @return GoodResult[]
         */
        public function findGoods(FindGoodsRequest $request): array
        {
            $boolQuery = new Query\BoolQuery();
            if ($request->activeOnly) {
                $boolQuery->addMust(new Query\Term(['active' => true]));
            }
            $range = [];
            if ($request->minPrice !== null) {
                $range['gte'] = $request->minPrice;
            }
            if ($request->maxPrice !== null) {
                $range['lte'] = $request->maxPrice;
            }
            if (count($range) > 0) {
                $boolQuery->addMust(new Query\Range('price', $range));
            }
            $boolQuery->addShould(new Query\Fuzzy('name', $request->search));
            $boolQuery->addShould(new Query\Fuzzy('description', $request->search));
            return array_map(
                static fn (HybridResult $result): GoodResult => new GoodResult(
                    $result->getTransformed(),
                    $result->getResult()->getScore(),
                ),
                $this->finder->findHybrid(new Query($boolQuery))
            );
        }
    }
    ```
3. Выполняем запрос Find goods v4 из Postman-коллекции и получаем результат с фильтрацией по цене
