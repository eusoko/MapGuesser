<?php namespace MapGuesser\Database\Query;

use Closure;
use MapGuesser\Interfaces\Database\IConnection;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Database\RawExpression;
use MapGuesser\Database\Utils;

class Select
{
    const CONDITION_WHERE = 0;

    const CONDITION_HAVING = 1;

    private IConnection $connection;

    private string $table;

    private string $idName = 'id';

    private array $tableAliases = [];

    private array $joins = [];

    private array $columns = [];

    private array $conditions = [self::CONDITION_WHERE => [], self::CONDITION_HAVING => []];

    private array $groups = [];

    private array $orders = [];

    private array $limit;

    public function __construct(IConnection $connection, ?string $table = null)
    {
        $this->connection = $connection;

        if ($table !== null) {
            $this->table = $table;
        }
    }

    public function setIdName(string $idName): Select
    {
        $this->idName = $idName;

        return $this;
    }

    public function setTableAliases(array $tableAliases): Select
    {
        $this->tableAliases = array_merge($this->tableAliases, $tableAliases);

        return $this;
    }

    public function from(string $table): Select
    {
        $this->table = $table;

        return $this;
    }

    public function columns(array $columns): Select
    {
        $this->columns = array_merge($this->columns, $columns);

        return $this;
    }

    public function innerJoin($table, $column1, string $relation, $column2): Select
    {
        $this->addJoin('INNER', $table, $column1, $relation, $column2);

        return $this;
    }

    public function leftJoin($table, $column1, string $relation, $column2): Select
    {
        $this->addJoin('LEFT', $table, $column1, $relation, $column2);

        return $this;
    }

    public function whereId($value): Select
    {
        $this->addWhereCondition('AND', $this->idName, '=', $value);

        return $this;
    }

    public function where($column, string $relation = null, $value = null): Select
    {
        $this->addWhereCondition('AND', $column, $relation, $value);

        return $this;
    }

    public function orWhere($column, string $relation = null, $value = null): Select
    {
        $this->addWhereCondition('OR', $column, $relation, $value);

        return $this;
    }

    public function having($column, string $relation = null, $value = null): Select
    {
        $this->addHavingCondition('AND', $column, $relation, $value);

        return $this;
    }

    public function orHaving($column, string $relation = null, $value = null): Select
    {
        $this->addHavingCondition('OR', $column, $relation, $value);

        return $this;
    }

    public function groupBy($column): Select
    {
        $this->groups[] = $column;

        return $this;
    }

    public function orderBy($column, string $type = 'ASC'): Select
    {
        $this->orders[] = [$column, $type];

        return $this;
    }

    public function limit(int $limit, int $offset = 0): Select
    {
        $this->limit = [$limit, $offset];

        return $this;
    }

    public function resetLimit(): void
    {
        $this->limit = null;
    }

    public function paginate(int $page, int $itemsPerPage)
    {
        $this->limit($itemsPerPage, ($page - 1) * $itemsPerPage);

        return $this;
    }

    public function execute(): IResultSet
    {
        list($query, $params) = $this->generateQuery();

        return $this->connection->prepare($query)->execute($params);
    }

    public function count(): int
    {
        if (count($this->groups) > 0 || count($this->conditions[self::CONDITION_HAVING]) > 0) {
            $orders = $this->orders;

            $this->orders = [];

            list($query, $params) = $this->generateQuery();

            $result = $this->connection->prepare('SELECT COUNT(*) num_rows FROM (' . $query . ') x')
                ->execute($params)
                ->fetch(IResultSet::FETCH_NUM);

            $this->orders = $orders;

            return $result[0];
        } else {
            $columns = $this->columns;
            $orders = $this->orders;

            $this->columns = [new RawExpression('COUNT(*) num_rows')];
            $this->orders = [];

            list($query, $params) = $this->generateQuery();

            $result = $this->connection->prepare($query)
                ->execute($params)
                ->fetch(IResultSet::FETCH_NUM);

            $this->columns = $columns;
            $this->orders = $orders;

            return $result[0];
        }
    }

    private function addJoin(string $type, $table, $column1, string $relation, $column2): void
    {
        $this->joins[] = [$type, $table, $column1, $relation, $column2];
    }

    private function addWhereCondition(string $logic, $column, string $relation, $value): void
    {
        $this->conditions[self::CONDITION_WHERE][] = [$logic, $column, $relation, $value];
    }

    private function addHavingCondition(string $logic, $column, string $relation, $value): void
    {
        $this->conditions[self::CONDITION_HAVING][] = [$logic, $column, $relation, $value];
    }

    private function generateQuery(): array
    {
        $queryString = 'SELECT ' . $this->generateColumns() . ' FROM ' . $this->generateTable($this->table, true);

        if (count($this->joins) > 0) {
            $queryString .= ' ' . $this->generateJoins();
        }

        if (count($this->conditions[self::CONDITION_WHERE]) > 0) {
            list($wheres, $whereParams) = $this->generateConditions(self::CONDITION_WHERE);

            $queryString .= ' WHERE ' .  $wheres;
        } else {
            $whereParams = [];
        }

        if (count($this->groups) > 0) {
            $queryString .= ' GROUP BY ' . $this->generateGroupBy();
        }

        if (count($this->conditions[self::CONDITION_HAVING]) > 0) {
            list($havings, $havingParams) = $this->generateConditions(self::CONDITION_HAVING);

            $queryString .= ' HAVING ' .  $havings;
        } else {
            $havingParams = [];
        }

        if (count($this->orders) > 0) {
            $queryString .= ' ORDER BY ' . $this->generateOrderBy();
        }

        if (isset($this->limit)) {
            $queryString .= ' LIMIT ' . $this->limit[1] . ', ' . $this->limit[0];
        }

        return [$queryString, array_merge($whereParams, $havingParams)];
    }

    private function generateTable($table, bool $defineAlias = false): string
    {
        if ($table instanceof RawExpression) {
            return (string) $table;
        }

        if (isset($this->tableAliases[$table])) {
            return ($defineAlias ? Utils::backtick($this->tableAliases[$table]) . ' ' . Utils::backtick($table) : Utils::backtick($table));
        }

        return Utils::backtick($table);
    }

    private function generateColumn($column): string
    {
        if ($column instanceof RawExpression) {
            return (string) $column;
        }

        if (is_array($column)) {
            $out = '';

            if ($column[0]) {
                $out .= $this->generateTable($column[0]) . '.';
            }

            $out .= Utils::backtick($column[1]);

            if (!empty($column[2])) {
                $out .= ' ' . Utils::backtick($column[2]);
            }

            return $out;
        } else {
            return Utils::backtick($column);
        }
    }

    private function generateColumns(): string
    {
        $columns = $this->columns;

        array_walk($columns, function (&$value, $key) {
            $value = $this->generateColumn($value);
        });

        return implode(',', $columns);
    }

    private function generateJoins(): string
    {
        $joins = $this->joins;

        array_walk($joins, function (&$value, $key) {
            $value = $value[0] . ' JOIN ' . $this->generateTable($value[1], true) . ' ON ' . $this->generateColumn($value[2]) . ' ' . $value[3] . ' ' . $this->generateColumn($value[4]);
        });

        return implode(' ', $joins);
    }

    private function generateConditions(string $type): array
    {
        $conditions = '';
        $params = [];

        foreach ($this->conditions[$type] as $condition) {
            list($logic, $column, $relation, $value) = $condition;

            if ($column instanceof Closure) {
                list($conditionsStringFragment, $paramsFragment) = $this->generateComplexConditionFragment($type, $column);
            } else {
                list($conditionsStringFragment, $paramsFragment) = $this->generateConditionFragment($condition);
            }

            if ($conditions !== '') {
                $conditions .= ' ' . $logic . ' ';
            }

            $conditions .= $conditionsStringFragment;
            $params = array_merge($params, $paramsFragment);
        }

        return [$conditions, $params];
    }

    private function generateConditionFragment(array $condition): array
    {
        list($logic, $column, $relation, $value) = $condition;

        if ($column instanceof RawExpression) {
            return [(string) $column, []];
        }

        $conditionsString = $this->generateColumn($column) . ' ';

        if ($value === null) {
            return [$conditionsString . ($relation == '=' ? 'IS NULL' : 'IS NOT NULL'), []];
        }

        $conditionsString .= strtoupper($relation) . ' ';;

        switch ($relation = strtolower($relation)) {
            case 'between':
                $params = [$value[0], $value[1]];

                $conditionsString .= '? AND ?';
                break;

            case 'in':
            case 'not in':
                $params = $value;

                if (count($value) > 0) {
                    $conditionsString .= '(' . implode(', ', array_fill(0, count($value), '?')) . ')';
                } else {
                    $conditionsString = $relation == 'in' ? '0' : '1';
                }
                break;

            default:
                $params = [$value];

                $conditionsString .= '?';
        }

        return [$conditionsString, $params];
    }

    private function generateComplexConditionFragment(string $type, Closure $conditionCallback): array
    {
        $instance = new static($this->connection, $this->table);
        $instance->tableAliases = $this->tableAliases;

        $conditionCallback($instance);

        list($conditions, $params) = $instance->generateConditions($type);

        return ['(' . $conditions . ')', $params];
    }

    private function generateGroupBy(): string
    {
        $groups = $this->groups;

        array_walk($groups, function (&$value, $key) {
            $value = $this->generateColumn($value);
        });

        return implode(',', $groups);
    }

    private function generateOrderBy(): string
    {
        $orders = $this->orders;

        array_walk($orders, function (&$value, $key) {
            $value = $this->generateColumn($value[0]) . ' ' . strtoupper($value[1]);
        });

        return implode(',', $orders);
    }
}
