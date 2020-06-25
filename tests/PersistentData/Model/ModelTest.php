<?php namespace MapGuesser\Tests\Util;

use MapGuesser\PersistentData\Model\Model;
use PHPUnit\Framework\TestCase;

class DummyModel extends Model
{
    protected static string $table = 'test_table';

    protected static array $fields = ['name', 'valid'];

    protected static array $relations = ['other_model' => OtherModel::class];

    private string $name;

    private bool $valid;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValid(): bool
    {
        return $this->valid;
    }
}

final class ModelTest extends TestCase
{
    public function testCanReturnTable(): void
    {
        $this->assertEquals('test_table', DummyModel::getTable());
    }

    public function testCanReturnFields(): void
    {
        $this->assertEquals(['id', 'name', 'valid'], DummyModel::getFields());
    }

    public function testCanReturnRelations(): void
    {
        $this->assertEquals(['other_model' => OtherModel::class], DummyModel::getRelations());
    }

    public function testCanBeConvertedToArray(): void
    {
        $model = new DummyModel();
        $model->setId(123);
        $model->setName('John');
        $model->setValid(true);

        $this->assertEquals([
            'id' => 123,
            'name' => 'John',
            'valid' => true
        ], $model->toArray());
    }

    public function testCanSaveAndResetSnapshot(): void
    {
        $model = new DummyModel();
        $model->setId(123);
        $model->setName('John');
        $model->setValid(true);

        $model->saveSnapshot();

        $this->assertEquals([
            'id' => 123,
            'name' => 'John',
            'valid' => true
        ], $model->getSnapshot());

        $model->resetSnapshot();

        $this->assertEquals([], $model->getSnapshot());
    }
}
