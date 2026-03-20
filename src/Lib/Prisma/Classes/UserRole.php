<?php

declare(strict_types=1);

namespace Lib\Prisma\Classes;

use Lib\Prisma\Model\IModel;
use PP\Validator;
use Brick\Math\BigInteger;
use Brick\Math\BigDecimal;
use PDO;
use InvalidArgumentException;
use LogicException;
use Exception;
use Throwable;
use RuntimeException;

class UserRole implements IModel
{
    protected string $_tableName;
    protected string $_primaryKey;
    protected array $_compositeKeys;
    protected array $_fieldsOnly;
    protected array $_fields;
    protected array $_fieldByRelationName;
    protected array $_fieldsRelatedWithKeys;
    protected array $_fieldsRelated;
    protected array $_tableFieldsOnly;
    protected array $_fieldsCombined;

    private array $_primaryKeyFields;
    private array $_primaryKeyAndUniqueFields;
    private PDO $_pdo;
    private string $_dbType;
    private string $_modelName;
    private array $_model;
    private array $_relationConditions = ['some', 'none', 'every'];

    public function __construct(PDO $pdo)
    {
        $this->_model = [
            'name' => 'UserRole',
            'dbName' => NULL,
            'schema' => NULL,
            'fields' => [
                [
                    'name' => 'id',
                    'kind' => 'scalar',
                    'isList' => false,
                    'isRequired' => true,
                    'isUnique' => false,
                    'isId' => true,
                    'isReadOnly' => false,
                    'hasDefaultValue' => true,
                    'type' => 'Int',
                    'nativeType' => NULL,
                    'default' => [
                        'name' => 'autoincrement',
                        'args' => [],
                    ],
                    'isGenerated' => false,
                    'isUpdatedAt' => false,
                ],
                [
                    'name' => 'name',
                    'kind' => 'scalar',
                    'isList' => false,
                    'isRequired' => true,
                    'isUnique' => true,
                    'isId' => false,
                    'isReadOnly' => false,
                    'hasDefaultValue' => false,
                    'type' => 'String',
                    'nativeType' => NULL,
                    'isGenerated' => false,
                    'isUpdatedAt' => false,
                ],
                [
                    'name' => 'user',
                    'kind' => 'object',
                    'isList' => true,
                    'isRequired' => true,
                    'isUnique' => false,
                    'isId' => false,
                    'isReadOnly' => false,
                    'hasDefaultValue' => false,
                    'type' => 'User',
                    'nativeType' => NULL,
                    'relationName' => 'UserToUserRole',
                    'relationFromFields' => [],
                    'relationToFields' => [],
                    'isGenerated' => false,
                    'isUpdatedAt' => false,
                ],
            ],
            'primaryKey' => NULL,
            'uniqueFields' => [],
            'uniqueIndexes' => [],
            'isGenerated' => false,
        ];

        $this->_fields = array_column($this->_model['fields'], null, 'name');
        $this->_fieldByRelationName = array_column($this->_model['fields'], null, 'relationName');
        $this->_fieldsOnly = ['id', 'name'];
        $this->_tableFieldsOnly = ['id', 'name'];
        $this->_fieldsRelated = ['user'];
        $this->_fieldsRelatedWithKeys = [
            'user' => [
                "relationFromFields" => ['roleId'],
                "relationToFields" => ['id']
            ]
        ];
        $this->_primaryKey = 'id';
        $this->_compositeKeys = [];
        $this->_primaryKeyFields = ['id'];
        $this->_primaryKeyAndUniqueFields = ['id', 'name'];
        $this->_fieldsCombined = ['id', 'name', 'user'];

        $this->_pdo = $pdo;
        $this->_dbType = $this->_pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $this->_modelName = 'UserRole';
        $this->_tableName = 'UserRole';

    }

    /**
     * Magic getter to access properties dynamically.
     *
     * This method allows dynamic access to properties of the UserRole class.
     * It checks if the requested property exists and returns its value.
     *
     * @param string $name The name of the property being accessed.
     * @return mixed The value of the requested property.
     * @throws Exception Throws an exception if the property does not exist.
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new Exception("Property '$name' does not exist in UserRole.");
    }

    private function detectAtomicOperation($value, string $fieldName, array $fieldMeta): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        $atomicOps = ['increment', 'decrement', 'multiply', 'divide'];
        $foundOps = array_intersect(array_keys($value), $atomicOps);

        if (empty($foundOps)) {
            return null;
        }

        if (count($foundOps) > 1) {
            throw new Exception(
                "Field '$fieldName' cannot have multiple atomic operations. " .
                    "Found: " . implode(', ', $foundOps)
            );
        }

        $operation = reset($foundOps);
        $operationValue = $value[$operation];

        $fieldType = $fieldMeta['type'] ?? null;
        $numericTypes = ['Int', 'BigInt', 'Float', 'Decimal'];

        if (!in_array($fieldType, $numericTypes, true)) {
            throw new Exception(
                "Atomic operation '$operation' can only be used on numeric fields. " .
                    "Field '$fieldName' is of type '$fieldType'."
            );
        }

        $isValidNumeric = is_numeric($operationValue)
            || $operationValue instanceof BigInteger
            || $operationValue instanceof BigDecimal;

        if (!$isValidNumeric) {
            throw new Exception(
                "The value for atomic operation '$operation' on field '$fieldName' must be numeric. " .
                    "Got: " . gettype($operationValue)
            );
        }

        if ($operationValue instanceof BigInteger || $operationValue instanceof BigDecimal) {
            $operationValue = $operationValue->__toString();
        }

        if ($operation === 'divide' && (float)$operationValue == 0) {
            throw new Exception(
                "Cannot divide field '$fieldName' by zero."
            );
        }

        return [
            'operation' => $operation,
            'value' => $operationValue
        ];
    }

    private function generateAtomicOperationSQL(
        string $fieldName,
        string $operation,
        $value,
        string $dbType
    ): string {
        $quotedField = PPHPUtility::quoteColumnName($dbType, $fieldName);
        $placeholder = ":atomic_{$fieldName}";

        switch ($operation) {
            case 'increment':
                return "$quotedField = $quotedField + $placeholder";

            case 'decrement':
                return "$quotedField = $quotedField - $placeholder";

            case 'multiply':
                return "$quotedField = $quotedField * $placeholder";

            case 'divide':
                if ($dbType === 'pgsql') {
                    return "$quotedField = ($quotedField::DECIMAL / $placeholder)";
                } elseif ($dbType === 'mysql') {
                    return "$quotedField = ($quotedField / $placeholder)";
                } else {
                    return "$quotedField = ($quotedField / $placeholder)";
                }

            default:
                throw new Exception("Unknown atomic operation: $operation");
        }
    }

    private function handleRelatedField(
        string $fieldName,
        array &$dataToProcess,
        array &$bindings,
        array &$fieldsToProcess,
        array &$placeholders
    ): void {
        foreach ($this->_fieldsRelatedWithKeys as $relationName => $relationFields) {
            
            if (in_array($fieldName, $relationFields['relationFromFields'])) {
                if (!array_key_exists($relationName, $dataToProcess)) {
                    throw new InvalidArgumentException("The required related field '$relationName' for '$fieldName' is missing in the provided data.");
                }

                $relationBindings = PPHPUtility::processRelation(
                    $this->_modelName,
                    $relationName,
                    $dataToProcess[$relationName],
                    $this->_pdo,
                    $this->_dbType,
                );

                foreach ($relationBindings as $fromField => $value) {
                    $bindings[$fromField] = $value;
                    $fieldsToProcess[] = $fromField;
                    $placeholders[] = ":$fromField";
                }

                unset($dataToProcess[$relationName]);
            } elseif ($fieldName === $relationName) {
                $relationBindings = PPHPUtility::processRelation(
                    $this->_modelName,
                    $relationName,
                    $dataToProcess[$relationName],
                    $this->_pdo,
                    $this->_dbType,
                );

                foreach ($relationBindings as $fromField => $value) {
                    $bindings[":$fromField"] = $value;
                    $fieldsToProcess[] = PPHPUtility::quoteColumnName($this->_dbType, $fromField) . " = :$fromField";
                    $placeholders[] = ":$fromField";
                }

                unset($dataToProcess[$relationName]);
            }
        }
    }

    private function buildRelationCondition(string $relationName, string $conditionType, array $nestedWhere, array $field, array &$bindings): string
    {
        if (!in_array($conditionType, ['some', 'none', 'every'])) {
            throw new Exception("Invalid condition type: $conditionType. Must be 'some', 'none', or 'every'.");
        }

        $nestedConditions = [];
        PPHPUtility::processConditions($nestedWhere, $nestedConditions, $bindings, $this->_dbType, 'j0');
        $nestedConditionSql = implode(" AND ", $nestedConditions);

        $relatedFieldKeys = $this->_fieldsRelatedWithKeys[$relationName];

        if (!empty($relatedFieldKeys['relationFromFields']) && !empty($relatedFieldKeys['relationToFields'])) {
            return $this->buildOneToManyRelationClause($relationName, $field, $nestedConditionSql, $relatedFieldKeys, $conditionType);
        } else {
            return $this->buildManyToManyRelationClause($relationName, $field, $nestedConditionSql, $conditionType);
        }
    }

    private function buildOneToManyRelationClause(
        string $relationName,
        array $field,
        string $nestedConditionSql,
        array $relatedFieldKeys,
        string $conditionType
    ): string {
        $relatedClass = "Lib\\Prisma\\Classes\\" . $field['type'];
        $relatedInstance = new $relatedClass($this->_pdo);
        $relatedTable = PPHPUtility::quoteColumnName(
            $this->_dbType,
            $relatedInstance->_tableName
        );
        $quotedTableName = PPHPUtility::quoteColumnName(
            $this->_dbType,
            $this->_tableName
        );

        $fromField = $relatedFieldKeys['relationFromFields'][0];
        $toField = $relatedFieldKeys['relationToFields'][0];

        $fromFieldExistsInRelated = isset($relatedInstance->_fields[$fromField]);

        if ($fromFieldExistsInRelated) {
            $relatedColumn = PPHPUtility::quoteColumnName($this->_dbType, $fromField);
            $currentColumn = PPHPUtility::quoteColumnName($this->_dbType, $toField);
            $joinCondition = "j0.$relatedColumn = $quotedTableName.$currentColumn";
        } else {
            $currentColumn = PPHPUtility::quoteColumnName($this->_dbType, $fromField);
            $relatedColumn = PPHPUtility::quoteColumnName($this->_dbType, $toField);
            $joinCondition = "j0.$relatedColumn = $quotedTableName.$currentColumn";
        }

        switch ($conditionType) {
            case 'some':
                return "EXISTS(SELECT 1 FROM $relatedTable AS j0 " .
                    "WHERE $nestedConditionSql " .
                    "AND $joinCondition)";

            case 'none':
                return "NOT EXISTS(SELECT 1 FROM $relatedTable AS j0 " .
                    "WHERE $nestedConditionSql " .
                    "AND $joinCondition)";

            case 'every':
                return "NOT EXISTS(SELECT 1 FROM $relatedTable AS j0 " .
                    "WHERE NOT ($nestedConditionSql) " .
                    "AND $joinCondition)";

            default:
                throw new Exception("Unsupported condition type: $conditionType");
        }
    }

    private function buildManyToManyRelationClause(string $relationName, array $field, string $nestedConditionSql, string $conditionType): string
    {
        $relatedClass = "Lib\\Prisma\\Classes\\" . $field['type'];
        $relatedInstance = new $relatedClass($this->_pdo);

        $implicitModelInfo = PPHPUtility::compareStringsAlphabetically($this->_modelName, $field['type']);
        $searchColumn = ($this->_modelName === $implicitModelInfo['A']) ? 'B' : 'A';
        $returnColumn = ($searchColumn === 'A') ? 'B' : 'A';

        $implicitTable = PPHPUtility::quoteColumnName($this->_dbType, $implicitModelInfo['Name']);
        $relatedTable = PPHPUtility::quoteColumnName($this->_dbType, $relatedInstance->_tableName);
        $quotedTableName = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);
        $primaryKeyQuoted = PPHPUtility::quoteColumnName($this->_dbType, $this->_primaryKey);

        $baseQuery = "SELECT t0.$returnColumn FROM $implicitTable AS t0 " .
            "INNER JOIN $relatedTable AS j0 ON (j0." . PPHPUtility::quoteColumnName($this->_dbType, 'id') . " = t0.$searchColumn) " .
            "WHERE %s " .
            "AND $quotedTableName.$primaryKeyQuoted = t0.$returnColumn " .
            "AND t0.$returnColumn IS NOT NULL";

        switch ($conditionType) {
            case 'some':
                return "EXISTS(" . sprintf($baseQuery, "($nestedConditionSql)") . ")";

            case 'none':
                return "NOT EXISTS(" . sprintf($baseQuery, "($nestedConditionSql)") . ")";

            case 'every':
                return "NOT EXISTS(" . sprintf($baseQuery, "NOT ($nestedConditionSql)") . ")";

            default:
                throw new Exception("Unsupported condition type: $conditionType");
        }
    }

    private function processNestedRelationConditions(array &$where, array &$conditions, array &$bindings, $depth = 0): void
    {
        if ($depth > 10) {
            throw new Exception("Maximum nesting depth exceeded in WHERE conditions.");
        }

        foreach (['AND', 'OR', 'NOT'] as $logicOp) {
            if (!isset($where[$logicOp])) {
                continue;
            }

            if ($logicOp === 'NOT') {
                $this->processNestedRelationConditions($where[$logicOp], $conditions, $bindings, $depth + 1);
            } else {
                $groupedConditions = [];
                $indicesToRemove = [];

                foreach ($where[$logicOp] as $index => $subCondition) {
                    $relationProcessed = false;

                    foreach ($this->_fieldsRelated as $relationName) {
                        if (!isset($subCondition[$relationName])) {
                            continue;
                        }

                        $field = $this->_fields[$relationName] ?? null;
                        if (!$field) {
                            continue;
                        }

                        $processed = false;
                        foreach ($this->_relationConditions as $conditionType) {
                            if (isset($subCondition[$relationName][$conditionType])) {
                                $existsClause = $this->buildRelationCondition(
                                    $relationName,
                                    $conditionType,
                                    $subCondition[$relationName][$conditionType],
                                    $field,
                                    $bindings
                                );
                                $groupedConditions[] = $existsClause;
                                $processed = true;
                                $relationProcessed = true;
                                break;
                            }
                        }

                        if (!$processed && is_array($subCondition[$relationName])) {
                            $relatedFieldKeys = $this->_fieldsRelatedWithKeys[$relationName];

                            if (!empty($relatedFieldKeys['relationFromFields']) && !empty($relatedFieldKeys['relationToFields'])) {
                                $relatedClass = "Lib\\Prisma\\Classes\\" . $field['type'];
                                $relatedInstance = new $relatedClass($this->_pdo);
                                $relatedTable = PPHPUtility::quoteColumnName($this->_dbType, $relatedInstance->_tableName);
                                $quotedTableName = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);

                                $nestedConditions = $subCondition[$relationName];
                                $nestedConditionsSql = [];
                                $nestedBindings = [];

                                $bindingPrefix = $logicOp . $index . '_' . $relationName . '_';

                                PPHPUtility::processConditions(
                                    $nestedConditions,
                                    $nestedConditionsSql,
                                    $nestedBindings,
                                    $this->_dbType,
                                    'j0',
                                    $bindingPrefix,
                                    $depth + 1
                                );

                                $bindings = array_merge($bindings, $nestedBindings);

                                $nestedConditionStr = implode(" AND ", $nestedConditionsSql);

                                $fromField = $relatedFieldKeys['relationFromFields'][0];
                                $toField = $relatedFieldKeys['relationToFields'][0];

                                $fromFieldExistsInRelated = isset($relatedInstance->_fields[$fromField]);

                                if ($fromFieldExistsInRelated) {
                                    $relatedColumn = PPHPUtility::quoteColumnName($this->_dbType, $fromField);
                                    $currentColumn = PPHPUtility::quoteColumnName($this->_dbType, $toField);
                                    $joinCondition = "j0.$relatedColumn = $quotedTableName.$currentColumn";
                                } else {
                                    $currentColumn = PPHPUtility::quoteColumnName($this->_dbType, $fromField);
                                    $relatedColumn = PPHPUtility::quoteColumnName($this->_dbType, $toField);
                                    $joinCondition = "j0.$relatedColumn = $quotedTableName.$currentColumn";
                                }

                                $existsClause = "EXISTS(SELECT 1 FROM $relatedTable AS j0 " .
                                    "WHERE $nestedConditionStr " .
                                    "AND $joinCondition)";

                                $groupedConditions[] = $existsClause;
                                $relationProcessed = true;
                            }
                        }
                    }

                    if ($relationProcessed && count($subCondition) === 1) {
                        $indicesToRemove[] = $index;
                    }
                }

                foreach ($indicesToRemove as $idx) {
                    unset($where[$logicOp][$idx]);
                }

                if (!empty($groupedConditions)) {
                    if ($logicOp === 'OR') {
                        $conditions[] = '(' . implode(' OR ', $groupedConditions) . ')';
                    } else {
                        $conditions[] = '(' . implode(' AND ', $groupedConditions) . ')';
                    }
                }

                if (empty($where[$logicOp])) {
                    unset($where[$logicOp]);
                }
            }
        }
    }

    /**
     * Performs an aggregate operation on the 'UserRole' table. This method allows for flexible aggregation queries
     * through a variety of operations such as COUNT, AVG, MAX, MIN, and SUM. It also supports conditional aggregation,
     * ordering, and pagination through cursors.
     *
     * @param array $operation An associative array specifying the aggregate operation(s) and conditions. 
     *                         The array can include keys like '_avg', '_count', '_max', '_min', '_sum' for aggregation functions,
     *                         and 'where', 'cursor', 'orderBy', 'skip', 'take' for conditions and pagination. 
     *                         The 'where' key is expected to contain an associative array for conditions (e.g., ['status' => 'active']).
     *                         Aggregation function keys should map to arrays specifying fields to be aggregated (e.g., ['_count' => ['field' => true]]).
     * @param string $format The format of the result. Currently, only 'array' format is supported and is the default value.
     *
     * @return UserRoleData Returns an object containing the results of the aggregate operation. The result will include keys corresponding to the specified
     *
     * @throws Exception Throws an exception if:
     *                    - The 'operation' parameter is not an associative array.
     *                    - Invalid keys are present in the 'operation' parameter.
     *                    - No valid aggregate function is specified in the 'operation' array.
     *                    - The database operation fails for any reason.
     *
     * @example 
     * $criteria = [
     *     '_count' => ['*' => true],
     *     'where' => ['status' => 'active'],
     * ];
     * $result = $prisma->userRole->aggregate($criteria);
     * Returns: ['_count' => ['*' => '<count_result>']]
     *
     * This method first validates the 'operation' parameter to ensure it is an associative array and contains valid keys.
     * It then constructs a SQL query based on the specified conditions and aggregate functions. The method supports complex
     * queries with conditions (WHERE clause), ordering (ORDER BY), and pagination (LIMIT and OFFSET) on the 'UserRole' table.
     * The actual database operation is executed using a prepared statement to prevent SQL injection. The result of the
     * aggregate operation(s) is processed and returned in the specified format.
     */
    public function aggregate(array $operation): object
    {
        if (PPHPUtility::checkArrayContents($operation) !== ArrayType::Associative) {
            throw new Exception("The 'operation' parameter must be an associative array.");
        }

        $acceptedCriteria = ['_avg', '_count', '_max', '_min', '_sum', 'cursor', 'orderBy', 'skip', 'take', 'where'];
        PPHPUtility::checkForInvalidKeys($operation, $acceptedCriteria, $this->_modelName);

        $where = $operation['where'] ?? [];
        $conditions = [];
        $bindings = [];

        $quotedTableName = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);

        if (isset($operation['cursor']) && is_array($operation['cursor'])) {
            foreach ($operation['cursor'] as $field => $value) {
                $fieldQuoted   = PPHPUtility::quoteColumnName($this->_dbType, $field);
                $conditions[]  = "$quotedTableName.$fieldQuoted >= :cursor_$field";
                $bindings[":cursor_$field"] = $value;
            }
        }

        $this->processNestedRelationConditions($where, $conditions, $bindings);

        foreach ($this->_fieldsRelated as $relationName) {
            $field = $this->_fields[$relationName] ?? [];
            if (array_key_exists($relationName, $where) && $field) {
                foreach ($this->_relationConditions as $conditionType) {
                    if (isset($where[$relationName][$conditionType])) {
                        $existsClause = $this->buildRelationCondition(
                            $relationName,
                            $conditionType,
                            $where[$relationName][$conditionType],
                            $field,
                            $bindings
                        );
                        $conditions[] = $existsClause;
                        unset($where[$relationName]);
                        continue 2;
                    }
                }

                $relatedFieldKeys = $this->_fieldsRelatedWithKeys[$relationName];
                if (!empty($relatedFieldKeys['relationFromFields']) && !empty($relatedFieldKeys['relationToFields'])) {
                    if ($where[$relationName] === null) {
                        $relationCondition = [$relatedFieldKeys['relationFromFields'][0] => null];
                    } else if (!empty($where[$relationName])) {
                        $relationCondition = is_array($where[$relationName])
                            ? array_combine($relatedFieldKeys['relationFromFields'], array_values($where[$relationName]))
                            : [$relatedFieldKeys['relationFromFields'][0] => $where[$relationName]];
                    }

                    PPHPUtility::processConditions($relationCondition, $conditions, $bindings, $this->_dbType, $quotedTableName);
                    unset($where[$relationName]);
                } else {
                    throw new Exception("Relation field not properly defined for '$relationName'");
                }
            }
        }

        if (!empty($where)) {
            PPHPUtility::processConditions($where, $conditions, $bindings, $this->_dbType, $quotedTableName);
        }

        $subQuery = "SELECT * FROM $quotedTableName";
        if (!empty($conditions)) {
            $subQuery .= " WHERE " . implode(' AND ', $conditions);
        }

        PPHPUtility::queryOptions($operation, $subQuery, $this->_dbType, $quotedTableName, false);
        $subAlias = 'sub';

        $aggregateFunctions = ['_avg' => 'AVG', '_count' => 'COUNT', '_max' => 'MAX', '_min' => 'MIN', '_sum' => 'SUM'];
        $sqlSelectParts = [];

        foreach ($aggregateFunctions as $key => $func) {
            if (!array_key_exists($key, $operation)) continue;

            if ($key === '_count' && $operation['_count'] === true) {
                $aliasQuoted = PPHPUtility::quoteColumnName($this->_dbType, '_all__count');
                $sqlSelectParts[] = "COUNT(*) AS $aliasQuoted";
                continue;
            }

            $fieldsReq = $operation[$key];
            if (!is_array($fieldsReq)) {
                throw new Exception("'$key' must be an array (or 'true' for _count).");
            }

            foreach ($fieldsReq as $field => $enabled) {
                if (!$enabled) continue;

                if ($key === '_count' && $field === '_all') {
                    $aliasQuoted = PPHPUtility::quoteColumnName($this->_dbType, '_all__count');
                    $sqlSelectParts[] = "COUNT(*) AS $aliasQuoted";
                    continue;
                }

                if (!isset($this->_fields[$field])) {
                    throw new Exception("Field '$field' does not exist in {$this->_modelName}.");
                }

                $qField = PPHPUtility::quoteColumnName($this->_dbType, $field);
                $alias = "{$field}_{$key}";
                $aliasQuoted = PPHPUtility::quoteColumnName($this->_dbType, $alias);
                $sqlSelectParts[] = "{$func}($subAlias.$qField) AS $aliasQuoted";
            }
        }

        if (empty($sqlSelectParts)) {
            throw new Exception('No valid aggregate function specified.');
        }

        $sql = "SELECT " . implode(', ', $sqlSelectParts) . " FROM ($subQuery) AS $subAlias";

        $stmt = $this->_pdo->prepare($sql);
        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch() ?: [];

        $aggregateResult = [];

        $castByFieldType = function (string $op, ?string $fieldName, $value) {
            if ($value === null) return null;

            if ($op === '_count') return (int)$value;

            $type = $fieldName && isset($this->_fields[$fieldName]['type'])
                ? $this->_fields[$fieldName]['type']
                : null;

            switch ($op) {
                case '_sum':
                    if ($type === 'Int')     return (int)$value;
                    if ($type === 'BigInt')  return (string)$value;
                    if ($type === 'Decimal') return (string)$value;
                    return is_numeric($value) ? +$value : $value;

                case '_avg':
                    if ($type === 'Decimal' || $type === 'BigInt') {
                        return (string)$value;
                    }
                    return is_numeric($value) ? (float)$value : $value;

                case '_min':
                case '_max':
                    if ($type === 'Int')     return (int)$value;
                    if ($type === 'BigInt')  return (string)$value;
                    if ($type === 'Decimal') return (string)$value;
                    if ($type === 'Boolean') return PPHPUtility::toBool($value);
                    if ($type === 'DateTime') return Validator::dateTime($value);
                    return $value;

                default:
                    return $value;
            }
        };

        foreach ($result as $alias => $value) {
            if (preg_match('/^(.*?)_(_avg|_count|_min|_max|_sum)$/', $alias, $m)) {
                $field     = $m[1];
                $op        = $m[2];

                $norm = $castByFieldType($op, $field !== '_all' ? $field : null, $value);

                if ($op === '_count' && $field === '_all') {
                    $aggregateResult[$op]['_all'] = $norm;
                } else {
                    $aggregateResult[$op][$field] = $norm;
                }
            }
        }

        if (isset($operation['_count']) && $operation['_count'] === true) {
            $aggregateResult['_count'] = ['_all' => (int)($aggregateResult['_count']['_all'] ?? 0)];
        }

        return (object) array_map(static fn($data) => (object) $data, $aggregateResult);
    }
    
    /**
     * Creates a new UserRole in the database.
     *
     * This method is designed to insert a new UserRole record into the database using provided data.
     * It is capable of handling related records through the dynamically defined relations in the UserRole model.
     * The method allows for selective field return and including related models in the response, enhancing flexibility and control
     * over the output.
     *
     * @param:
     * - `array $data`: An associative array that contains the data for the new User record.
     *   The array may also include 'select' and 'include' keys for selective field retrieval
     *   and including related models in the result, respectively. The 'data' key within this array
     *   is required and contains the actual data for the User record.
     *
     * @return UserRoleData The newly created UserRoleData record.
     *
     * @throws:
     * - `Exception` if the 'data' key is not provided or is not an associative array.
     * - `Exception` if both 'include' and 'select' keys are used simultaneously.
     * - `Exception` for any error encountered during the creation process.
     *
     * Example:
     * ```
     * Example of creating a new UserRole with related profile and roles
     * $result = $prisma->userRole->create([
     *   'data' => [
     *     'property' => 'value',
     * ]);
     * ```
     *
     * Notes:
     * - The method checks for required fields in the 'data' array and validates their types,
     *   ensuring data integrity before attempting to create the record.
     * - It supports complex operations such as connecting or creating related records based on
     *   predefined relations, offering a powerful way to manage related data efficiently.
     * - Transaction management is utilized to ensure that all database operations are executed
     *   atomically, rolling back changes in case of any error, thus maintaining data consistency.
     */
    public function create(array $data): object
    {
        if (!array_key_exists('data', $data)) {
            throw new InvalidArgumentException("The 'data' key is required when creating a new $this->_modelName.");
        }

        if (!is_array($data['data'])) {
            throw new InvalidArgumentException("The 'data' key must contain an associative array.");
        }

        if (!empty($data['include']) && !empty($data['select'])) {
            throw new LogicException("You cannot use both 'include' and 'select' simultaneously.");
        }

        $acceptedCriteria = ['data', 'select', 'include', 'omit'];
        PPHPUtility::checkForInvalidKeys($data, $acceptedCriteria, $this->_modelName);

        $dataToCreate = $data['data'];
        $select = $data['select'] ?? [];
        $include = $data['include'] ?? [];
        $omit = $data['omit'] ?? [];
        $primaryKeyFields = [];
        $insertFields = [];
        $placeholders = [];
        $bindings = [];

        $quotedTableName = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);

        PPHPUtility::checkFieldsExist(array_merge($dataToCreate, $select, $include), $this->_fields, $this->_modelName);

        try {
            $this->_pdo->beginTransaction();

            foreach ($this->_fields as $field) {
                $fieldName = $field['name'];
                $fieldType = $field['type'];
                $isRequired = $field['isRequired'] ?? false;
                $kind = $field['kind'] ?? 'scalar';
                $isList = $field['isList'] ?? false;
                $isUpdatedAt = $field['isUpdatedAt'] ?? false;
                $dbName = $field['dbName'] ?? $fieldName;
                $isReadOnly = $field['isReadOnly'] ?? false;
                $hasDefaultValue = $field['hasDefaultValue'] ?? false;

                if ($isUpdatedAt) {
                    if (!array_key_exists($fieldName, $dataToCreate) || empty($dataToCreate[$fieldName])) {
                        $bindings[$dbName] = date('Y-m-d H:i:s');
                        $insertFields[] = $dbName;
                        $placeholders[] = ":$dbName";
                    } else {
                        $validateMethodName = lcfirst($fieldType);
                        $bindings[$dbName] = Validator::$validateMethodName($dataToCreate[$fieldName]);
                        $insertFields[] = $dbName;
                        $placeholders[] = ":$dbName";
                    }
                    continue;
                }

                if ($hasDefaultValue) {
                    if (!array_key_exists($fieldName, $dataToCreate)) {
                        if (is_array($field['default']) && !empty($field['default']['name'])) {
                            switch ($field['default']['name']) {
                                case 'uuid':
                                    $bindings[$dbName] = \Symfony\Component\Uid\Uuid::v4();
                                    break;
                                case 'ulid':
                                    $bindings[$dbName] = \Symfony\Component\Uid\Ulid::generate();
                                    break;
                                case 'cuid':
                                    $bindings[$dbName] = \CaliCastle\Cuid::make();
                                    break;
                                case 'now':
                                    $bindings[$dbName] = date('Y-m-d H:i:s');
                                    break;
                                default:
                                    continue 2;
                            }
                        } elseif (!is_array($field['default'])) {
                            if ($kind === 'enum') {
                                $enumClass       = 'Lib\\Prisma\\Classes\\' . $fieldType;
                                $validatedValue  = Validator::enumClass($field['default'], $enumClass);

                                if ($validatedValue === null) {
                                    throw new InvalidArgumentException(
                                        "Invalid default value '{$field['default']}' for enum '$fieldType' in '$fieldName'."
                                    );
                                }

                                $bindings[$dbName] = $isList
                                    ? json_encode($validatedValue)
                                    : $validatedValue;
                            } else {
                                $validateMethodName = lcfirst($fieldType);
                                $validatedValue     = Validator::$validateMethodName($field['default']);

                                if ($fieldType === 'Boolean') {
                                    $bindings[$dbName] = $validatedValue ? 1 : 0;
                                } elseif ($validatedValue instanceof BigInteger || $validatedValue instanceof BigDecimal) {
                                    $bindings[$dbName] = $validatedValue->__toString();
                                } else {
                                    $bindings[$dbName] = $validatedValue;
                                }
                            }
                        } else {
                            continue;
                        }

                        $insertFields[]  = $dbName;
                        $placeholders[]  = ":$dbName";
                        continue;
                    }
                }

                if ($kind === 'object') {
                    continue;
                }

                if (!array_key_exists($fieldName, $dataToCreate)) {
                    if ($isReadOnly) {
                        $foundRelationMapping = false;
                        foreach ($this->_fieldsRelatedWithKeys as $relationName => $relationFields) {
                            if (in_array($fieldName, $relationFields['relationFromFields'])) {
                                $foundRelationMapping = true;

                                if ($isRequired) {
                                    if (!array_key_exists($relationName, $dataToCreate)) {
                                        throw new Exception(
                                            "Missing required relation data for field '$fieldName' via relation '$relationName' in model '{$this->_modelName}'. " .
                                                "Expected one of the valid actions: " . implode(', ', ['create', 'connect', 'connectOrCreate'])
                                        );
                                    }

                                    $validActions = ['create', 'connect', 'connectOrCreate'];
                                    $relationData = $dataToCreate[$relationName];
                                    if (empty($relationData)) {
                                        throw new Exception(
                                            "Missing required relation data for field '$fieldName' via relation '$relationName' in model '{$this->_modelName}'. " .
                                                "Expected one of the valid actions: " . implode(', ', $validActions)
                                        );
                                    }
                                    foreach ($relationData as $action => $records) {
                                        if (!in_array($action, $validActions, true)) {
                                            throw new Exception(
                                                "Invalid relation action '$action' for field '$fieldName' via relation '$relationName' in model '{$this->_modelName}'. " .
                                                    "Allowed actions: " . implode(', ', $validActions)
                                            );
                                        }
                                    }
                                }

                                if (array_key_exists($relationName, $dataToCreate)) {
                                    $this->handleRelatedField($fieldName, $dataToCreate, $bindings, $insertFields, $placeholders);
                                }
                                continue 2;
                            }
                        }

                        if (!$foundRelationMapping && $isRequired) {
                            throw new Exception("Missing required field '$fieldName' type '{$fieldType}' in model '{$this->_modelName}'.");
                        }
                    } elseif ($isRequired) {
                        throw new Exception("Missing required field '$fieldName' type '{$fieldType}' in model '{$this->_modelName}'.");
                    }
                }

                if ($kind === 'enum' && array_key_exists($fieldName, $dataToCreate)) {

                    $enumClass = 'Lib\\Prisma\\Classes\\' . $fieldType;
                    $rawValue  = $dataToCreate[$fieldName];

                    $validated = Validator::enumClass($rawValue, $enumClass);
                    if ($validated === null) {
                        throw new InvalidArgumentException(
                            "Valor inválido para enum '$fieldType' en campo '$fieldName'."
                        );
                    }

                    if ($isList) {
                        $bindings[$dbName]  = json_encode($validated);
                    } else {
                        $bindings[$dbName]  = $validated;
                    }

                    $insertFields[] = $dbName;
                    $placeholders[] = ":$dbName";
                    continue;
                }

                if (array_key_exists($fieldName, $dataToCreate)) {
                    $value = $dataToCreate[$fieldName];
                    $validateMethodName = lcfirst($fieldType);

                    if ($value === null) {
                        $bindings[$dbName] = null;
                    } elseif ($fieldType === 'Decimal') {
                        $scale = 30;
                        if (!empty($field['nativeType'][1])) {
                            $scale = intval($field['nativeType'][1][1]);
                        }

                        $validated = Validator::$validateMethodName($value, $scale);
                        $bindings[$dbName] = ($validated instanceof BigInteger || $validated instanceof BigDecimal)
                            ? $validated->__toString()
                            : $validated;
                    } else {
                        $validated = Validator::$validateMethodName($value);
                        if ($fieldType === 'Boolean') {
                            $bindings[$dbName] = $validated ? 1 : 0;
                        } elseif ($validated instanceof BigInteger || $validated instanceof BigDecimal) {
                            $bindings[$dbName] = $validated->__toString();
                        } else {
                            $bindings[$dbName] = $validated;
                        }
                    }

                    $insertFields[]  = $dbName;
                    $placeholders[]  = ":$dbName";
                    continue;
                } elseif (!$isRequired) {
                    $insertFields[] = $dbName;
                    $placeholders[] = "NULL";
                    continue;
                }
            }

            $fieldStr = implode(', ', array_map(
                fn($f) => PPHPUtility::quoteColumnName($this->_dbType, $f),
                $insertFields
            ));
            $placeholderStr = implode(', ', $placeholders);

            if (!$this->_primaryKey && !empty($this->_compositeKeys)) {
                $primaryKeyFields = $this->_compositeKeys;
            } else {
                $primaryKeyFields = [$this->_primaryKey];
            }

            if ($this->_dbType == 'pgsql' || $this->_dbType == 'sqlite') {
                $returningFields = implode(', ', array_map(
                    fn($f) => PPHPUtility::quoteColumnName($this->_dbType, $f),
                    $primaryKeyFields
                ));
                $sql = "INSERT INTO $quotedTableName ($fieldStr) VALUES ($placeholderStr) RETURNING $returningFields";
            } else {
                $sql = "INSERT INTO $quotedTableName ($fieldStr) VALUES ($placeholderStr)";
            }

            $stmt = $this->_pdo->prepare($sql);

            foreach ($bindings as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();

            $lastInsertId = null;

            if ($this->_dbType == 'pgsql' || $this->_dbType == 'sqlite') {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!empty($this->_compositeKeys)) {
                    $lastInsertId = [];
                    foreach ($this->_compositeKeys as $key) {
                        $lastInsertId[$key] = $result[$key] ?? null;
                    }
                } else {
                    $lastInsertId = $result[$this->_primaryKey] ?? null;
                }
            } elseif ($this->_dbType == 'mysql') {
                if (!empty($this->_compositeKeys)) {
                    $lastInsertId = [];
                    foreach ($this->_compositeKeys as $key) {
                        if (isset($bindings[$key])) {
                            $lastInsertId[$key] = $bindings[$key];
                        }
                    }
                } else {
                    $lastInsertId = $this->_pdo->lastInsertId();

                    if (empty($lastInsertId) && isset($bindings[$this->_primaryKey])) {
                        $lastInsertId = $bindings[$this->_primaryKey];
                    }
                }
            }

            if (empty($lastInsertId)) {
                if (!empty($this->_compositeKeys)) {
                    $lastInsertId = [];
                    foreach ($this->_compositeKeys as $key) {
                        if (isset($bindings[$key])) {
                            $lastInsertId[$key] = $bindings[$key];
                        }
                    }
                } elseif ($this->_primaryKey && isset($bindings[$this->_primaryKey])) {
                    $lastInsertId = $bindings[$this->_primaryKey];
                }
            }

            foreach ($this->_fieldsRelatedWithKeys as $fieldRelatedName => $fieldsRelated) {
                if (!array_key_exists($fieldRelatedName, $dataToCreate) || !array_key_exists($fieldRelatedName, $this->_fields)) {
                    continue;
                }

                $validActions  = ['create', 'connect', 'connectOrCreate'];
                $relationData  = $dataToCreate[$fieldRelatedName];

                foreach ($relationData as $action => $records) {
                    if (!in_array($action, $validActions, true)) {
                        throw new Exception(
                            "Invalid relation action '$action'. Allowed: " . implode(', ', $validActions)
                        );
                    }

                    if (!is_array($records)) {
                        throw new Exception(
                            "Expected an array for '$fieldRelatedName.$action' but got " . gettype($records)
                        );
                    }

                    if (array_keys($records) !== range(0, count($records) - 1)) {
                        $records = [$records];
                    }

                    $actionReference = [];
                    if (empty($fieldsRelated['relationFromFields']) && empty($fieldsRelated['relationToFields'])) {
                        $relatedFieldType = $this->_fields[$fieldRelatedName]['type'];
                        $nestedCreate = [];
                        foreach ($records as $record) {
                            $nestedCreate[] = [
                                $relatedFieldType => $record,
                                $this->_modelName => [$this->_primaryKey => $lastInsertId]
                            ];
                        }

                        $actionReference = [
                            $action => $nestedCreate
                        ];
                    } else {
                        $records = array_map(function ($record) use ($fieldsRelated, $lastInsertId) {
                            foreach ($fieldsRelated['relationFromFields'] as $index => $fromField) {
                                $toField = $fieldsRelated['relationToFields'][$index];
                                $record[$fromField] = is_array($lastInsertId) && isset($lastInsertId[$toField])
                                    ? $lastInsertId[$toField]
                                    : $lastInsertId;
                            }
                            return $record;
                        }, $records);

                        $actionReference = [$action => $records];
                    }

                    PPHPUtility::processRelation(
                        $this->_modelName,
                        $fieldRelatedName,
                        $actionReference,
                        $this->_pdo,
                        $this->_dbType,
                        false,
                    );
                }
            }

            $selectOrInclude = '';
            $selectedFields = [];
            if (!empty($select)) {
                $selectOrInclude = 'select';
                $selectedFields = $select;
            } elseif (!empty($include)) {
                $selectOrInclude = 'include';
                $selectedFields = $include;
            }

            $query = [];

            if (is_array($lastInsertId)) {
                $query['where'] = $lastInsertId;
            } else {
                if (empty($primaryKeyFields)) {
                    throw new RuntimeException(
                        "No primary or composite key is defined for model {$this->_modelName}."
                    );
                }

                $pkField = $primaryKeyFields[0];

                if ($pkField === null || $pkField === '') {
                    throw new RuntimeException(
                        "Primary key field is not set for model {$this->_modelName}."
                    );
                }

                $query['where'] = [$pkField => $lastInsertId];
            }

            if (!empty($selectedFields)) {
                $query[$selectOrInclude] = $selectedFields;
            }

            if (!empty($omit)) {
                $query['omit'] = $omit;
            }

            $result = $this->findUnique($query);
            $this->_pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->_pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Bulk Create UserRole – Fast, Safe, and Flexible
     *
     * Effortlessly add multiple UserRole records to your database in a single, atomic operation.
     * This method is perfect for onboarding, migrations, or any scenario where you need to insert many UserRole records at once.
     *
     * Key Features:
     * - Accepts an array of UserRole records—add as many as you need in one call.
     * - Runs inside a transaction: if any record fails, nothing is saved.
     * - Validates required fields for every UserRole before saving.
     * - Optionally skip duplicates (if your database supports it) by setting 'skipDuplicates' => true.
     *
     * Usage Example:
     *   $data = [
     *     ['name' => 'Alice', 'email' => 'alice@example.com'],
     *     ['name' => 'Bob', 'email' => 'bob@example.com']
     *   ];
     *   $result = $prisma->userRole->createMany([
     *     'data' => $data,
     *     'skipDuplicates' => true // optional
     *   ]);
     *   $result = (object)['count' => 2]
     *
     * @param:
     * - array $data: Must include a 'data' key with an array of UserRole records.
     * - skipDuplicates (optional): Set to true to ignore duplicates if supported by your DB.
     *
     * @return:
     * - An object like (object)['count' => N], where N is the number of UserRoles created.
     *
     * @throws:
     * - Exception if 'data' is missing, empty, or not a list of arrays.
     * - Exception if the database operation fails.
     *
     * This method gives you a simple, reliable way to create many users at once—no loops, no hassle, just results.
     */
    public function createMany(array $data): object
    {
        if (!isset($data['data'])) {
            throw new InvalidArgumentException("The 'data' key is required when calling createMany().");
        }

        if (!is_array($data['data']) || $data['data'] === [] || !array_is_list($data['data'])) {
            throw new InvalidArgumentException("'data' must be a non‑empty **list** of associative arrays.");
        }

        if (!empty($data['include']) || !empty($data['select']) || !empty($data['omit'])) {
            throw new LogicException("createMany() does not support 'include', 'select' or 'omit'.");
        }

        $acceptedCriteria = ['data', 'skipDuplicates'];
        PPHPUtility::checkForInvalidKeys($data, $acceptedCriteria, $this->_modelName);

        $skipDuplicates = $data['skipDuplicates'] ?? false;
        $rows          = $data['data'];

        $scalarFields = [];
        foreach ($this->_fields as $meta) {
            if (($meta['kind'] ?? '') !== 'object') {
                $scalarFields[$meta['name']] = $meta;
            }
        }

        $columnUnion = [];
        foreach ($rows as $idx => $row) {
            if (!is_array($row)) {
                throw new InvalidArgumentException("Each element inside 'data' must be an associative array (row #$idx).");
            }

            foreach ($row as $k => $_) {
                if (!isset($scalarFields[$k])) {
                    throw new LogicException("Field '$k' cannot be provided inside createMany(); nested relation actions are not supported.");
                }
            }
            $columnUnion = array_unique(array_merge($columnUnion, array_keys($row)));
        }

        foreach ($scalarFields as $name => $meta) {
            if (($meta['isRequired'] ?? false) && !($meta['hasDefaultValue'] ?? false)) {
                $columnUnion[] = $name;
            }
        }
        $columnUnion = array_unique($columnUnion);

        $quotedTable  = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);
        $insertFields = array_map(fn($f) => PPHPUtility::quoteColumnName($this->_dbType, $f), $columnUnion);

        $allPlaceholders = [];
        $allBindings     = [];
        $rowIndex        = 0;

        foreach ($rows as $row) {
            $placeholders = [];

            foreach ($columnUnion as $fieldName) {
                $meta = $scalarFields[$fieldName] ?? null;
                if (!$meta) {
                    continue;
                }

                $dbName = $meta['dbName'] ?? $fieldName;
                $placeholder = ":{$dbName}_{$rowIndex}";

                $valueProvided = array_key_exists($fieldName, $row);
                $fieldType     = $meta['type'];
                $kind   = $meta['kind']  ?? 'scalar';
                $isList = $meta['isList'] ?? false;

                if ($kind === 'enum') {
                    if (!$valueProvided) {
                        if ($meta['isRequired'] ?? false) {
                            throw new Exception("Missing required enum field '$fieldName' in createMany().");
                        }
                        $placeholders[] = 'NULL';
                        continue;
                    }

                    $enumClass = 'Lib\\Prisma\\Classes\\' . $fieldType;
                    $casted = Validator::enumClass($row[$fieldName], $enumClass);
                    if ($casted === null) {
                        throw new InvalidArgumentException(
                            "Valor inválido para enum '$fieldType' en fila #{$rowIndex}, campo '$fieldName'."
                        );
                    }

                    $allBindings[ltrim($placeholder, ':')] = $isList
                        ? json_encode($casted)
                        : $casted;

                    $placeholders[] = $placeholder;
                    continue;
                }

                if ($meta['isUpdatedAt'] ?? false) {
                    $value = $valueProvided ? Validator::{lcfirst($fieldType)}($row[$fieldName])
                        : date('Y-m-d H:i:s');
                } elseif ($valueProvided) {
                    $validate = lcfirst($fieldType);
                    if ($fieldType === 'Decimal') {
                        $scale = 30;
                        if (!empty($meta['nativeType'][1])) {
                            $scale = intval($meta['nativeType'][1][1]);
                        }
                        $value = Validator::$validate($row[$fieldName], $scale);
                    } else {
                        $value = Validator::$validate($row[$fieldName]);
                    }
                    if ($fieldType === 'Boolean') {
                        $value = $value ? 1 : 0;
                    } elseif ($value instanceof BigInteger || $value instanceof BigDecimal) {
                        $value = $value->__toString();
                    }
                } else {
                    if ($meta['hasDefaultValue'] ?? false) {
                        $def = $meta['default'];
                        if (is_array($def) && !empty($def['name'])) {
                            switch ($def['name']) {
                                case 'uuid':
                                    $value = \Symfony\Component\Uid\Uuid::v4();
                                    break;
                                case 'ulid':
                                    $value = \Symfony\Component\Uid\Ulid::generate();
                                    break;
                                case 'cuid':
                                    $value = \CaliCastle\Cuid::make();
                                    break;
                                case 'now':
                                    $value = date('Y-m-d H:i:s');
                                    break;
                                case 'autoincrement':
                                    $placeholders[] = 'DEFAULT';
                                    continue 2;
                                default:
                                    $value = $def;
                            }
                        } else {
                            $value = $def;
                        }
                    } elseif (($meta['isRequired'] ?? false)) {
                        throw new Exception("Missing required field '$fieldName' in createMany().");
                    } else {
                        $placeholders[] = 'NULL';
                        continue;
                    }
                }

                $placeholders[]               = $placeholder;
                $allBindings[ltrim($placeholder, ':')] = $value;
            }

            $allPlaceholders[] = '(' . implode(', ', $placeholders) . ')';
            $rowIndex++;
        }

        $fieldStr        = implode(', ', $insertFields);
        $placeholderStr  = implode(', ', $allPlaceholders);
        $sqlPrefix       = "INSERT INTO $quotedTable ($fieldStr) VALUES ";
        $sqlSuffix       = '';

        if ($skipDuplicates) {
            if ($this->_dbType === 'mysql') {
                $sqlPrefix = "INSERT IGNORE INTO $quotedTable ($fieldStr) VALUES ";
            } elseif (in_array($this->_dbType, ['pgsql', 'sqlite'], true)) {
                $sqlSuffix = ' ON CONFLICT DO NOTHING';
            }
        }

        $sql = $sqlPrefix . $placeholderStr . $sqlSuffix;

        try {
            $this->_pdo->beginTransaction();
            $stmt = $this->_pdo->prepare($sql);
            foreach ($allBindings as $ph => $v) {
                $stmt->bindValue(":" . $ph, $v);
            }

            $stmt->execute();
            $affected = $stmt->rowCount();
            $this->_pdo->commit();
            return (object)['count' => $affected];
        } catch (Throwable $e) {
            $this->_pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Bulk Create UserRole and Return Created Records
     *
     * This method allows you to create multiple UserRole records in a single operation and returns the created records.
     * It supports skipping duplicates and allows for selective field retrieval.
     *
     * @param array $data An associative array containing:
     * - 'data': A list of associative arrays representing the UserRole records to create.
     * - 'skipDuplicates': (optional) If true, skips records that would cause a duplicate key error.
     * - 'select': (optional) An associative array specifying which fields to return.
     * - 'include': (optional) An associative array specifying related models to include in the result.
     * - 'omit': (optional) An associative array specifying fields to omit from the result.
     *
     * @return array An array of created UserRoleData objects.
     *
     * @throws InvalidArgumentException if 'data' is not provided or is not a list of associative arrays.
     * @throws LogicException if both 'include' and 'select' are used simultaneously.
     */
    public function createManyAndReturn(array $data): array
    {
        if (!isset($data['data'])) {
            throw new InvalidArgumentException("The 'data' key is required when calling createManyAndReturn().");
        }

        if (!is_array($data['data']) || $data['data'] === [] || !array_is_list($data['data'])) {
            throw new InvalidArgumentException("'data' must be a non‑empty **list** of associative arrays.");
        }

        if (!empty($data['include']) && !empty($data['select'])) {
            throw new LogicException("You cannot use both 'include' and 'select' simultaneously.");
        }

        $acceptedCriteria = ['data', 'skipDuplicates', 'select', 'include', 'omit'];
        PPHPUtility::checkForInvalidKeys($data, $acceptedCriteria, $this->_modelName);

        $skipDuplicates = $data['skipDuplicates'] ?? false;
        $select = $data['select'] ?? [];
        $include = $data['include'] ?? [];
        $omit = $data['omit'] ?? [];
        $rows = $data['data'];

        $scalarFields = [];
        foreach ($this->_fields as $meta) {
            if (($meta['kind'] ?? '') !== 'object') {
                $scalarFields[$meta['name']] = $meta;
            }
        }

        $columnUnion = [];
        foreach ($rows as $idx => $row) {
            if (!is_array($row)) {
                throw new InvalidArgumentException("Each element inside 'data' must be an associative array (row #$idx).");
            }

            foreach ($row as $k => $_) {
                if (!isset($scalarFields[$k])) {
                    throw new LogicException("Field '$k' cannot be provided inside createManyAndReturn(); nested relation actions are not supported.");
                }
            }
            $columnUnion = array_unique(array_merge($columnUnion, array_keys($row)));
        }

        foreach ($scalarFields as $name => $meta) {
            if (($meta['isRequired'] ?? false) && !($meta['hasDefaultValue'] ?? false)) {
                $columnUnion[] = $name;
            }
        }
        $columnUnion = array_unique($columnUnion);

        $quotedTable = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);
        $insertFields = array_map(fn($f) => PPHPUtility::quoteColumnName($this->_dbType, $f), $columnUnion);

        $allPlaceholders = [];
        $allBindings = [];
        $rowIndex = 0;

        foreach ($rows as $row) {
            $placeholders = [];

            foreach ($columnUnion as $fieldName) {
                $meta = $scalarFields[$fieldName] ?? null;
                if (!$meta) {
                    continue;
                }

                $dbName = $meta['dbName'] ?? $fieldName;
                $placeholder = ":{$dbName}_{$rowIndex}";

                $valueProvided = array_key_exists($fieldName, $row);
                $fieldType = $meta['type'];
                $kind = $meta['kind'] ?? 'scalar';
                $isList = $meta['isList'] ?? false;

                if ($kind === 'enum') {
                    if (!$valueProvided) {
                        if ($meta['isRequired'] ?? false) {
                            throw new Exception("Missing required enum field '$fieldName' in createManyAndReturn().");
                        }
                        $placeholders[] = 'NULL';
                        continue;
                    }

                    $enumClass = 'Lib\\Prisma\\Classes\\' . $fieldType;
                    $casted = Validator::enumClass($row[$fieldName], $enumClass);
                    if ($casted === null) {
                        throw new InvalidArgumentException(
                            "Invalid value for enum '$fieldType' in row #{$rowIndex}, field '$fieldName'."
                        );
                    }

                    $allBindings[ltrim($placeholder, ':')] = $isList ? json_encode($casted) : $casted;
                    $placeholders[] = $placeholder;
                    continue;
                }

                if ($meta['isUpdatedAt'] ?? false) {
                    $value = $valueProvided ? Validator::{lcfirst($fieldType)}($row[$fieldName]) : date('Y-m-d H:i:s');
                } elseif ($valueProvided) {
                    $validate = lcfirst($fieldType);
                    if ($fieldType === 'Decimal') {
                        $scale = 30;
                        if (!empty($meta['nativeType'][1])) {
                            $scale = intval($meta['nativeType'][1][1]);
                        }
                        $value = Validator::$validate($row[$fieldName], $scale);
                    } else {
                        $value = Validator::$validate($row[$fieldName]);
                    }
                    if ($fieldType === 'Boolean') {
                        $value = $value ? 1 : 0;
                    } elseif ($value instanceof BigInteger || $value instanceof BigDecimal) {
                        $value = $value->__toString();
                    }
                } else {
                    if ($meta['hasDefaultValue'] ?? false) {
                        $def = $meta['default'];
                        if (is_array($def) && !empty($def['name'])) {
                            switch ($def['name']) {
                                case 'uuid':
                                    $value = \Symfony\Component\Uid\Uuid::v4();
                                    break;
                                case 'ulid':
                                    $value = \Symfony\Component\Uid\Ulid::generate();
                                    break;
                                case 'cuid':
                                    $value = \CaliCastle\Cuid::make();
                                    break;
                                case 'now':
                                    $value = date('Y-m-d H:i:s');
                                    break;
                                case 'autoincrement':
                                    $placeholders[] = 'DEFAULT';
                                    continue 2;
                                default:
                                    $value = $def;
                            }
                        } else {
                            $value = $def;
                        }
                    } elseif (($meta['isRequired'] ?? false)) {
                        throw new Exception("Missing required field '$fieldName' in createManyAndReturn().");
                    } else {
                        $placeholders[] = 'NULL';
                        continue;
                    }
                }

                $placeholders[] = $placeholder;
                $allBindings[ltrim($placeholder, ':')] = $value;
            }

            $allPlaceholders[] = '(' . implode(', ', $placeholders) . ')';
            $rowIndex++;
        }

        $fieldStr = implode(', ', $insertFields);
        $placeholderStr = implode(', ', $allPlaceholders);

        $primaryKeyField = !$this->_primaryKey && !empty($this->_compositeKeys)
            ? implode(', ', $this->_compositeKeys)
            : $this->_primaryKey;

        $sqlPrefix = "INSERT INTO $quotedTable ($fieldStr) VALUES ";
        $sqlSuffix = '';

        if ($skipDuplicates) {
            if ($this->_dbType === 'mysql') {
                $sqlPrefix = "INSERT IGNORE INTO $quotedTable ($fieldStr) VALUES ";
            } elseif (in_array($this->_dbType, ['pgsql', 'sqlite'], true)) {
                $sqlSuffix = ' ON CONFLICT DO NOTHING';
            }
        }

        if ($this->_dbType === 'pgsql' || $this->_dbType === 'sqlite') {
            $sqlSuffix .= " RETURNING $primaryKeyField";
        }

        $sql = $sqlPrefix . $placeholderStr . $sqlSuffix;

        try {
            $this->_pdo->beginTransaction();

            $stmt = $this->_pdo->prepare($sql);
            foreach ($allBindings as $ph => $v) {
                $stmt->bindValue(":" . $ph, $v);
            }

            $stmt->execute();

            $insertedIds = [];
            if ($this->_dbType === 'pgsql' || $this->_dbType === 'sqlite') {
                $insertedIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $affected = $stmt->rowCount();
                if ($affected > 0) {
                    $lastId = $this->_pdo->lastInsertId();
                    for ($i = 0; $i < $affected; $i++) {
                        $insertedIds[] = $lastId + $i;
                    }
                }
            }

            if (empty($insertedIds)) {
                $this->_pdo->commit();
                return [];
            }

            $fetchQuery = [
                'where' => [$primaryKeyField => ['in' => $insertedIds]]
            ];

            if (!empty($select)) {
                $fetchQuery['select'] = $select;
            }

            if (!empty($include)) {
                $fetchQuery['include'] = $include;
            }

            if (!empty($omit)) {
                $fetchQuery['omit'] = $omit;
            }

            $results = $this->findMany($fetchQuery);

            $this->_pdo->commit();
            return $results;
        } catch (Throwable $e) {
            $this->_pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Retrieves a single UserRole record matching specified criteria.
     * 
     * Searches for a unique UserRole based on the provided filter criteria within `$criteria`.
     * It returns the UserRole data as either an associative array or an object, based on the `$format` parameter.
     * This method supports filtering (`where`), field selection (`select`), and inclusion of related models (`include`).
     * If no matching UserRole is found, an empty array is returned. The method includes comprehensive error handling for invalid inputs and parameter conflicts.
     *
     * @param array $criteria Filter criteria with keys:
     *  - 'where': Conditions to filter UserRole records.
     *  - 'select': Fields of the UserRole to return.
     *  - 'include': Related models to include in the result.
     * @return UserRoleData|null The UserRoleData record matching the criteria.
     * 
     * @throws Exception If 'where' condition is missing or not an associative array.
     * @throws Exception If both 'include' and 'select' are provided, as they are mutually exclusive.
     * @throws Exception If invalid or conflicting parameters are supplied.
     * 
     * @example
     * To find a UserRole by ID, select specific fields, and include related models:
     * $result = $prisma->userRole->findUnique([
     *   'where' => ['id' => 'someUserId'],
     *   'select' => ['name' => true, 'email' => true, 'profile' => true],
     * ]);
     * 
     * @example
     * To find a User by email and include related models:
     * $result = $prisma->userRole->findUnique([
     *  'where' => ['email' => 'john@example.com'],
     *  'include' => ['profile' => true, 'posts' => true],
     * ]);
     */
    public function findUnique(array $criteria): ?object
    {
        if (!array_key_exists('where', $criteria)) {
            throw new InvalidArgumentException("The 'where' key is required when finding a unique record in $this->_modelName.");
        }

        if (!is_array($criteria['where'])) {
            throw new InvalidArgumentException("The 'where' key must contain an associative array.");
        }

        if (!empty($criteria['include']) && !empty($criteria['select'])) {
            throw new LogicException("You cannot use both 'include' and 'select' simultaneously.");
        }

        $acceptedCriteria = ['where', 'select', 'include', 'omit'];
        PPHPUtility::checkForInvalidKeys($criteria, $acceptedCriteria, $this->_modelName);

        $where = $criteria['where'];
        $select = $criteria['select'] ?? [];
        $include = $criteria['include'] ?? [];
        $omit = $criteria['omit'] ?? [];
        $includeForJoin = [];
        $primaryEntityFields = [];
        $relatedEntityFields = [];
        $includes = [];
        $conditions = [];
        $bindings = [];
        $joins = [];
        $selectFields = [];
        $orderBy = $criteria['orderBy'] ?? [];

        $whereHasUniqueKey = false;
        foreach ($this->_primaryKeyAndUniqueFields as $key) {
            if (array_key_exists($key, $where)) {
                $whereHasUniqueKey = true;
                break;
            }
        }

        if (!$whereHasUniqueKey) {
            throw new Exception("No valid 'where' conditions provided for finding a unique record in $this->_modelName.");
        }

        $quotedTableName = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);

        $timestamp = "";
        $hasPrimaryKey = false;
        foreach ($this->_primaryKeyFields as $key) {
            if (isset($select[$key])) {
                $hasPrimaryKey = true;
                break;
            }
        }

        $hasPrimaryKeyProcessed = false;
        foreach ($this->_fieldsRelated as $relationName) {
            if (!array_key_exists($relationName, $select)) {
                continue;
            }

            if (!$hasPrimaryKeyProcessed && !$hasPrimaryKey) {
                $primaryEntityFields = array_merge($primaryEntityFields, $this->_primaryKeyFields);
                $hasPrimaryKeyProcessed = true;
            }

            $includes[$relationName] = $select[$relationName];

            $relationKeyToSelect = $this->_fieldsRelatedWithKeys[$relationName] ?? null;
            if (!empty($relationKeyToSelect['relationFromFields'])) {
                $fromFields = $relationKeyToSelect['relationFromFields'];
                if (!empty(array_intersect($fromFields, array_keys($this->_fields)))) {
                    $primaryEntityFields = array_merge($primaryEntityFields, $fromFields);
                }
            }
        }

        if (!empty($orderBy)) {
            foreach ($this->_fieldsRelated as $relationName) {
                if (isset($orderBy[$relationName])) {
                    $includeForJoin = array_merge($includeForJoin, [$relationName => true]);
                }
            }
        }

        PPHPUtility::checkIncludes($include, $relatedEntityFields, $includes, $this->_fields, $this->_modelName);
        PPHPUtility::checkFieldsExistWithReferences($select, $relatedEntityFields, $primaryEntityFields, $this->_fieldsRelated, $this->_fields, $this->_modelName, $timestamp);

        foreach ($this->_fieldsRelated as $relationName) {
            $field = $this->_fields[$relationName] ?? [];
            if (array_key_exists($relationName, $where) && $field) {
                $relatedClass = "Lib\\Prisma\\Classes\\" . $field['type'];
                $relatedInstance = new $relatedClass($this->_pdo);
                $tableName = PPHPUtility::quoteColumnName($this->_dbType, $relatedInstance->_tableName);
                $relatedFieldKeys = $this->_fieldsRelatedWithKeys[$relationName];

                foreach ($this->_relationConditions as $conditionType) {
                    if (isset($where[$relationName][$conditionType])) {
                        $existsClause = $this->buildRelationCondition(
                            $relationName,
                            $conditionType,
                            $where[$relationName][$conditionType],
                            $field,
                            $bindings
                        );
                        $conditions[] = $existsClause;
                        unset($where[$relationName]);
                        continue 2;
                    }
                }

                if (!empty($relatedFieldKeys['relationFromFields']) && !empty($relatedFieldKeys['relationToFields'])) {
                    $joinConditions = [];

                    foreach ($relatedFieldKeys['relationFromFields'] as $index => $fromField) {
                        $toField = $relatedFieldKeys['relationToFields'][$index] ?? null;
                        if ($toField) {
                            $quotedFromField = PPHPUtility::quoteColumnName($this->_dbType, $fromField);
                            $quotedToField = PPHPUtility::quoteColumnName($this->_dbType, $toField);

                            if (array_key_exists($fromField, $this->_fields)) {
                                $joinConditions[] = "$quotedTableName.$quotedFromField = $tableName.$quotedToField";
                            } else {
                                $joinConditions[] = "$tableName.$quotedFromField = $quotedTableName.$quotedToField";
                            }
                        }
                    }

                    if (!empty($joinConditions)) {
                        $joins[] = "LEFT JOIN $tableName ON " . implode(" AND ", $joinConditions);
                    }

                    if ($where[$relationName] === null) {
                        $relationCondition = [$relatedFieldKeys['relationFromFields'][0] => null];
                    } else if (!empty($where[$relationName])) {
                        $relationCondition = is_array($where[$relationName])
                            ? array_combine($relatedFieldKeys['relationFromFields'], array_values($where[$relationName]))
                            : [$relatedFieldKeys['relationFromFields'][0] => $where[$relationName]];
                    }

                    PPHPUtility::processConditions($relationCondition, $conditions, $bindings, $this->_dbType, $tableName);

                    unset($where[$relationName]);
                } else {
                    throw new Exception("Relation field not properly defined for '$relationName'");
                }
            }
        }

        if (!empty($includeForJoin)) {
            PPHPUtility::buildJoinsRecursively(
                $includeForJoin,
                $quotedTableName,
                $joins,
                $selectFields,
                $this->_pdo,
                $this->_dbType,
                $this
            );
        }

        if (empty($primaryEntityFields)) {
            $selectFields = array_map(function ($field) use ($quotedTableName) {
                $quotedField = PPHPUtility::quoteColumnName($this->_dbType, $field);
                return "$quotedTableName.$quotedField";
            }, $this->_tableFieldsOnly);
        } else {
            $selectFields = array_map(function ($field) use ($quotedTableName) {
                $quotedField = PPHPUtility::quoteColumnName($this->_dbType, $field);
                return "$quotedTableName.$quotedField";
            }, $primaryEntityFields);
        }

        $sql = "SELECT " . implode(', ', $selectFields) . " FROM $quotedTableName";

        if (!empty($joins)) {
            $sql .= " " . implode(' ', $joins);
        }

        $this->processNestedRelationConditions($where, $conditions, $bindings);

        PPHPUtility::processConditions($where, $conditions, $bindings, $this->_dbType, $quotedTableName);

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        PPHPUtility::queryOptions($criteria, $sql, $this->_dbType, $quotedTableName);

        if (empty($conditions)) {
            throw new Exception("No valid 'where' conditions provided for finding a unique record in UserRole.");
        }

        $stmt = $this->_pdo->prepare($sql);
        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $record = $stmt->fetch();

        if (!$record) {
            return null;
        }

        $record = PPHPUtility::populateIncludedRelations($record, $includes, $this->_fields, $this->_fieldsRelatedWithKeys, $this->_pdo, $this->_dbType);

        if (!empty($select)) {
            foreach (array_keys($record) as $key) {
                if (!isset($select[$key])) {
                    unset($record[$key]);
                }
            }
        }

        if (!empty($omit)) {
            PPHPUtility::checkFieldsExist($omit, $this->_fields, $this->_modelName);

            foreach (array_keys($omit) as $fieldToOmit) {
                unset($record[$fieldToOmit]);
            }
        }

        $record = PPHPUtility::normalizeRowTypes($record, $this->_fields);
        return (object) $record;
    }

    /**
     * Retrieves a unique UserRole record based on specified criteria.
     *
     * This method is a wrapper around `findUnique` that throws an exception if no record is found.
     * It is useful when you expect exactly one UserRole to match the criteria and want to handle the case where none exists.
     *
     * @param array $criteria Criteria to find the unique UserRole, same as in `findUnique`.
     * @return UserRoleData The UserRoleData record matching the criteria.
     * @throws Exception If no UserRole record matches the criteria.
     */
    public function findUniqueOrThrow(array $criteria): object
    {
        $result = $this->findUnique($criteria);

        if ($result === null) {
            throw new Exception("No {$this->_modelName} record found matching the unique criteria.");
        }

        return $result;
    }

    /**
     * Retrieves multiple UserRole records based on specified filter criteria.
     *
     * This method allows for a comprehensive query with support for filtering, ordering, pagination,
     * selective field retrieval, cursor-based pagination, and including related models. It returns an empty array
     * if no UserRoles match the criteria. This approach ensures flexibility and efficiency in fetching data
     * according to diverse requirements.
     *
     * @param array $criteria Query parameters including:
     *  - 'where': Filter criteria for records.
     *  - 'orderBy': Record ordering logic.
     *  - 'take': Number of records to return, useful for pagination.
     *  - 'skip': Number of records to skip, useful for pagination.
     *  - 'cursor': Cursor for pagination, identifying a specific record to start from.
     *  - 'select': Fields to include in the return value.
     *  - 'include': Related models to include in the result.
     *  - 'distinct': Returns only distinct records if set.
     * @return UserRoleData[] An array of UserRoleData objects or an empty array
     * 
     * @example
     * Retrieve UserRole with cursor-based pagination:
     * $result = $prisma->userRole->findMany([
     *   'cursor' => ['id' => 'someUserId'],
     *   'take' => 5
     * ]);
     * 
     * Select specific fields of UserRole:
     * $result = $prisma->userRole->findMany([
     *   'select' => ['name' => true, 'email' => true],
     *   'take' => 10
     * ]);
     * 
     * Include related models in the results:
     * $result = $prisma->userRole->findMany([
     *   'include' => ['posts' => true],
     *   'take' => 5
     * ]);
     * 
     * @throws Exception If 'include' and 'select' are used together, as they are mutually exclusive.
     */
    public function findMany(array $criteria = []): array
    {
        if (isset($criteria['where']) && !is_array($criteria['where'])) {
            throw new Exception("Invalid 'where' provided - must be an array.");
        }

        if (isset($criteria['include']) && isset($criteria['select'])) {
            throw new Exception("You can't use both 'include' and 'select' at the same time.");
        }

        $acceptedCriteria = ['where', 'orderBy', 'take', 'skip', 'cursor', 'select', 'include', 'distinct', 'omit'];
        PPHPUtility::checkForInvalidKeys($criteria, $acceptedCriteria, $this->_modelName);

        $where = $criteria['where'] ?? [];
        $select = $criteria['select'] ?? [];
        $include = $criteria['include'] ?? [];
        $omit = $criteria['omit'] ?? [];
        $includeForJoin = [];
        $distinct = isset($criteria['distinct']) && $criteria['distinct'] ? 'DISTINCT' : '';
        $primaryEntityFields = [];
        $relatedEntityFields = [];
        $includes = [];
        $joins = [];
        $selectFields = [];
        $conditions = [];
        $bindings = [];
        $orderBy = $criteria['orderBy'] ?? [];

        $quotedTableName = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);

        $timestamp = "";
        $hasPrimaryKey = false;
        foreach ($this->_primaryKeyFields as $key) {
            if (isset($select[$key])) {
                $hasPrimaryKey = true;
                break;
            }
        }

        $hasPrimaryKeyProcessed = false;
        foreach ($this->_fieldsRelated as $relationName) {
            if (!array_key_exists($relationName, $select)) {
                continue;
            }

            if (!$hasPrimaryKeyProcessed && !$hasPrimaryKey) {
                $primaryEntityFields = array_merge($primaryEntityFields, $this->_primaryKeyFields);
                $hasPrimaryKeyProcessed = true;
            }

            $includes[$relationName] = $select[$relationName];

            $relationKeyToSelect = $this->_fieldsRelatedWithKeys[$relationName] ?? null;
            if (!empty($relationKeyToSelect['relationFromFields'])) {
                $fromFields = $relationKeyToSelect['relationFromFields'];
                if (!empty(array_intersect($fromFields, array_keys($this->_fields)))) {
                    $primaryEntityFields = array_merge($primaryEntityFields, $fromFields);
                }
            }
        }

        if (!empty($orderBy)) {
            foreach ($this->_fieldsRelated as $relationName) {
                if (isset($orderBy[$relationName])) {
                    $includeForJoin = array_merge($includeForJoin, [$relationName => true]);
                }
            }
        }

        PPHPUtility::checkIncludes($include, $relatedEntityFields, $includes, $this->_fields, $this->_modelName);
        PPHPUtility::checkFieldsExistWithReferences($select, $relatedEntityFields, $primaryEntityFields, $this->_fieldsRelated, $this->_fields, $this->_modelName, $timestamp);

        if (isset($criteria['cursor']) && is_array($criteria['cursor'])) {
            foreach ($criteria['cursor'] as $field => $value) {
                $select[$field] = ['>=' => $value];
                $fieldQuoted = PPHPUtility::quoteColumnName($this->_dbType, $field);
                $conditions[] = "$fieldQuoted >= :cursor_$field";
                $bindings[":cursor_$field"] = $value;
            }
            if (!isset($select['skip'])) {
                $select['skip'] = 1;
            }
        }

        foreach ($this->_fieldsRelated as $relationName) {
            $field = $this->_fields[$relationName] ?? [];
            if (array_key_exists($relationName, $where) && $field) {
                $relatedClass = "Lib\\Prisma\\Classes\\" . $field['type'];
                $relatedInstance = new $relatedClass($this->_pdo);
                $tableName = PPHPUtility::quoteColumnName($this->_dbType, $relatedInstance->_tableName);
                $relatedFieldKeys = $this->_fieldsRelatedWithKeys[$relationName];

                foreach ($this->_relationConditions as $conditionType) {
                    if (isset($where[$relationName][$conditionType])) {
                        $existsClause = $this->buildRelationCondition(
                            $relationName,
                            $conditionType,
                            $where[$relationName][$conditionType],
                            $field,
                            $bindings
                        );
                        $conditions[] = $existsClause;
                        unset($where[$relationName]);
                        continue 2;
                    }
                }

                if (!empty($relatedFieldKeys['relationFromFields']) && !empty($relatedFieldKeys['relationToFields'])) {
                    $joinConditions = [];

                    foreach ($relatedFieldKeys['relationFromFields'] as $index => $fromField) {
                        $toField = $relatedFieldKeys['relationToFields'][$index] ?? null;
                        if ($toField) {
                            $quotedFromField = PPHPUtility::quoteColumnName($this->_dbType, $fromField);
                            $quotedToField = PPHPUtility::quoteColumnName($this->_dbType, $toField);

                            if (array_key_exists($fromField, $this->_fields)) {
                                $joinConditions[] = "$quotedTableName.$quotedFromField = $tableName.$quotedToField";
                            } else {
                                $joinConditions[] = "$tableName.$quotedFromField = $quotedTableName.$quotedToField";
                            }
                        }
                    }

                    if (!empty($joinConditions)) {
                        $joins[] = "LEFT JOIN $tableName ON " . implode(" AND ", $joinConditions);
                    }

                    if ($where[$relationName] === null) {
                        $relationCondition = [$relatedFieldKeys['relationFromFields'][0] => null];
                    } else if (!empty($where[$relationName])) {
                        $relationCondition = is_array($where[$relationName])
                            ? array_combine($relatedFieldKeys['relationFromFields'], array_values($where[$relationName]))
                            : [$relatedFieldKeys['relationFromFields'][0] => $where[$relationName]];
                    }

                    PPHPUtility::processConditions($relationCondition, $conditions, $bindings, $this->_dbType, $tableName);

                    unset($where[$relationName]);
                } else {
                    throw new Exception("Relation field not properly defined for '$relationName'");
                }
            }
        }

        if (isset($include['_count'])) {
            $countSelect = $include['_count']['select'] ?? [];
            $counter = 0;

            foreach ($countSelect as $internalRelation => $isCountRequested) {
                if ($isCountRequested !== true) {
                    continue;
                }

                $counter++;

                $relationKeys = $this->_fieldsRelatedWithKeys[$internalRelation] ?? [
                    'relationFromFields' => [],
                    'relationToFields' => []
                ];

                $isSelfReferential = isset($this->_fields[$internalRelation]['type'])
                    && $this->_fields[$internalRelation]['type'] === $this->_modelName;

                if ($isSelfReferential && empty($relationKeys['relationFromFields']) && empty($relationKeys['relationToFields'])) {
                    $relationName = $this->_fields[$internalRelation]['relationName'] ?? null;

                    foreach ($this->_fields as $fieldName => $fieldMeta) {
                        if (
                            $fieldName !== $internalRelation
                            && ($fieldMeta['relationName'] ?? null) === $relationName
                            && !empty($this->_fieldsRelatedWithKeys[$fieldName]['relationFromFields'])
                        ) {

                            $oppositeKeys = $this->_fieldsRelatedWithKeys[$fieldName];
                            $relationKeys = [
                                'relationFromFields' => $oppositeKeys['relationFromFields'],
                                'relationToFields' => $oppositeKeys['relationToFields']
                            ];
                            break;
                        }
                    }
                }

                if (!$isSelfReferential && empty($relationKeys['relationFromFields']) && empty($relationKeys['relationToFields'])) {
                    $relatedModelType = $this->_fields[$internalRelation]['type'] ?? $internalRelation;

                    $imp              = PPHPUtility::compareStringsAlphabetically($this->_modelName, $relatedModelType);
                    $pivotTable       = PPHPUtility::quoteColumnName($this->_dbType, $imp['Name']);
                    $pivotColumnA     = 'A';
                    $pivotColumnB     = 'B';
                    $pivotAQuoted     = PPHPUtility::quoteColumnName($this->_dbType, $pivotColumnA);
                    $pivotBQuoted     = PPHPUtility::quoteColumnName($this->_dbType, $pivotColumnB);

                    if ($imp['A'] === $relatedModelType) {
                        $relatedFkCol = $pivotAQuoted;
                        $mainFkCol    = $pivotBQuoted;
                    } else {
                        $relatedFkCol = $pivotBQuoted;
                        $mainFkCol    = $pivotAQuoted;
                    }

                    $alias       = "aggr_selection_{$counter}_" . ucfirst($internalRelation);
                    $primaryKey  = PPHPUtility::quoteColumnName($this->_dbType, $this->_primaryKey);

                    $relatedClass = "Lib\\Prisma\\Classes\\" . $relatedModelType;
                    $relatedInst  = new $relatedClass($this->_pdo);
                    $relatedTable = PPHPUtility::quoteColumnName($this->_dbType, $relatedInst->_tableName);
                    $aggrColName   = "_aggr_count_{$internalRelation}";
                    $aggrColQuoted = PPHPUtility::quoteColumnName($this->_dbType, $aggrColName);
                    $aliasQuoted   = PPHPUtility::quoteColumnName($this->_dbType, $alias);

                    $subq = "(SELECT {$pivotTable}.{$mainFkCol} AS pivot_main, COUNT(*) AS {$aggrColQuoted} 
                    FROM {$relatedTable} 
                    LEFT JOIN {$pivotTable} ON ({$relatedTable}.{$primaryKey} = {$pivotTable}.{$relatedFkCol}) 
                    WHERE 1=1 
                    GROUP BY {$pivotTable}.{$mainFkCol})";

                    $joins[]        = "LEFT JOIN {$subq} AS {$aliasQuoted} ON ({$quotedTableName}.{$primaryKey} = {$aliasQuoted}.pivot_main)";
                    $selectFields[] = "COALESCE({$aliasQuoted}.{$aggrColQuoted}, 0) AS {$aggrColQuoted}";

                    unset($include['_count']);
                    continue;
                }

                if (empty($relationKeys['relationFromFields']) || empty($relationKeys['relationToFields'])) {
                    throw new Exception("Relation keys not defined for {$internalRelation}");
                }

                $foreignKeyField = PPHPUtility::quoteColumnName($this->_dbType, $relationKeys['relationFromFields'][0]);
                $localKeyField = PPHPUtility::quoteColumnName($this->_dbType, $relationKeys['relationToFields'][0]);

                if (!isset($this->_fields[$internalRelation])) {
                    throw new Exception("Relation field not found in model for " . $internalRelation);
                }
                $relatedClass = "Lib\\Prisma\\Classes\\" . $this->_fields[$internalRelation]['type'];
                $relatedInstance = new $relatedClass($this->_pdo);
                $relatedTableName = PPHPUtility::quoteColumnName($this->_dbType, $relatedInstance->_tableName);

                $alias = "aggr_selection_{$counter}_" . ucfirst($internalRelation);
                $aliasQuoted   = PPHPUtility::quoteColumnName($this->_dbType, $alias);
                $aggrColName   = "_aggr_count_$internalRelation";
                $aggrColQuoted = PPHPUtility::quoteColumnName($this->_dbType, $aggrColName);

                $subQuery = "(SELECT $relatedTableName.$foreignKeyField, COUNT(*) AS {$aggrColQuoted} 
                FROM $relatedTableName 
                WHERE 1=1 
                GROUP BY $relatedTableName.$foreignKeyField)";
                
                $joins[] = "LEFT JOIN $subQuery AS {$aliasQuoted} ON ($quotedTableName.$localKeyField = {$aliasQuoted}.$foreignKeyField)";
                $selectFields[] = "COALESCE({$aliasQuoted}.{$aggrColQuoted}, 0) AS {$aggrColQuoted}";

                unset($include['_count']);
            }
        }

        if (!empty($includeForJoin)) {
            PPHPUtility::buildJoinsRecursively(
                $includeForJoin,
                $quotedTableName,
                $joins,
                $selectFields,
                $this->_pdo,
                $this->_dbType,
                $this
            );
        }

        $primarySelectFields = empty($primaryEntityFields)
            ? array_map(function ($field) use ($quotedTableName) {
                $quotedField = PPHPUtility::quoteColumnName($this->_dbType, $field);
                return "$quotedTableName.$quotedField";
            }, $this->_tableFieldsOnly)
            : array_map(function ($field) use ($quotedTableName) {
                $quotedField = PPHPUtility::quoteColumnName($this->_dbType, $field);
                return "$quotedTableName.$quotedField";
            }, $primaryEntityFields);

        $selectFields = array_merge($primarySelectFields, $selectFields);

        $sql = "SELECT $distinct " . implode(', ', $selectFields) . " FROM $quotedTableName";

        if (!empty($joins)) {
            $sql .= " " . implode(' ', $joins);
        }

        $this->processNestedRelationConditions($where, $conditions, $bindings);

        PPHPUtility::processConditions($where, $conditions, $bindings, $this->_dbType, $quotedTableName);

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        PPHPUtility::queryOptions($criteria, $sql, $this->_dbType, $quotedTableName);

        $stmt = $this->_pdo->prepare($sql);
        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $records = $stmt->fetchAll();

        if (!$records) {
            return [];
        }

        if ($records) {
            foreach ($records as &$record) {
                foreach ($record as $key => $value) {
                    if (strpos($key, '_aggr_count_') === 0) {
                        $relation = substr($key, strlen('_aggr_count_'));
                        if (!isset($record['_count'])) {
                            $record['_count'] = [];
                        }
                        $record['_count'][$relation] = $value;
                        unset($record[$key]);
                    }
                }

                if (isset($record['_count']) && is_array($record['_count'])) {
                    $record['_count'] = (object) $record['_count'];
                }
            }
        }

        $records = PPHPUtility::populateIncludedRelations($records, $includes, $this->_fields, $this->_fieldsRelatedWithKeys, $this->_pdo, $this->_dbType);

        if (!empty($select)) {
            foreach ($records as &$record) {
                foreach (array_keys($record) as $key) {
                    if (!isset($select[$key])) {
                        unset($record[$key]);
                    }
                }
            }
            unset($record);
        }

        if (!empty($omit)) {
            PPHPUtility::checkFieldsExist($omit, $this->_fields, $this->_modelName);

            foreach ($records as &$record) {
                foreach (array_keys($omit) as $fieldToOmit) {
                    unset($record[$fieldToOmit]);
                }
            }
            unset($record);
        }

        $records = array_map(
            fn(array $row) => PPHPUtility::normalizeRowTypes($row, $this->_fields),
            $records
        );
        return array_map(fn($data) => (object) $data, $records);
    }

    /**
     * Retrieves the first UserRole record that matches specified filter criteria.
     *
     * Designed to efficiently find and return the first UserRole record matching the provided criteria.
     * This method is optimized for scenarios where only the first matching record is needed, reducing
     * overhead compared to fetching multiple records. It supports filtering, ordering, selective field
     * retrieval, and including related models. Returns an empty array if no match is found.
     *
     * Parameters:
     * - @param array $criteria Associative array of query parameters, which may include:
     *   - 'where': Filter criteria for searching UserRole records.
     *   - 'orderBy': Specifies the order of records.
     *   - 'select': Fields to include in the result.
     *   - 'include': Related models to include in the results.
     *   - 'take': Limits the number of records returned, useful for limiting results to a single record or a specific number of records.
     *   - 'skip': Skips a number of records, useful in conjunction with 'take' for pagination.
     *   - 'cursor': Cursor-based pagination, specifying the record to start retrieving records from.
     *   - 'distinct': Ensures the query returns only distinct records based on the specified field(s).
     *
     * The inclusion of 'take', 'skip', 'cursor', and 'distinct' parameters extends the method's flexibility, allowing for more
     * controlled data retrieval strategies, such as pagination or retrieving unique records. It's important to note that while
     * some of these parameters ('take', 'skip', 'cursor') may not be commonly used with a method intended to fetch the first
     * matching record, they offer additional control for advanced query constructions.
     *
     * Returns:
     * @return UserRoleData|null The UserRoleData record matching the criteria.
     *
     * Examples:
     * Find a UserRole by email, returning specific fields:
     * $result = $prisma->userRole->findFirst([
     *   'where' => ['email' => 'user@example.com'],
     *   'select' => ['id', 'email', 'name']
     * ]);
     * Find an active UserRole, include their posts, ordered by name:
     * $result = $prisma->userRole->findFirst([
     *   'where' => ['active' => true],
     *   'orderBy' => 'name',
     *   'include' => ['posts' => true]
     * ]);
     *
     * Exception Handling:
     * - Throws Exception if 'include' and 'select' are used together, as they are mutually exclusive.
     * - Throws Exception if no valid 'where' filter is provided, ensuring purposeful searches.
     *
     * This method simplifies querying for a single record, offering control over the search through
     * filtering, sorting, and defining the scope of the returned data. It's invaluable for efficiently
     * retrieving specific records or subsets of fields.
     */
    public function findFirst(array $criteria = []): ?object
    {
        if (isset($criteria['where']) && !is_array($criteria['where'])) {
            throw new Exception("Invalid 'where' provided - must be an array.");
        }

        if (isset($criteria['include']) && isset($criteria['select'])) {
            throw new LogicException("You cannot use both 'include' and 'select' simultaneously.");
        }

        $acceptedCriteria = ['where', 'orderBy', 'take', 'skip', 'cursor', 'select', 'include', 'distinct', 'omit'];
        PPHPUtility::checkForInvalidKeys($criteria, $acceptedCriteria, $this->_modelName);

        $where = $criteria['where'] ?? [];
        $select = $criteria['select'] ?? [];
        $include = $criteria['include'] ?? [];
        $omit = $criteria['omit'] ?? [];
        $includeForJoin = [];
        $distinct = isset($criteria['distinct']) && $criteria['distinct'] ? 'DISTINCT' : '';
        $primaryEntityFields = [];
        $relatedEntityFields = [];
        $includes = [];
        $joins = [];
        $selectFields = [];
        $conditions = [];
        $bindings = [];
        $orderBy = $criteria['orderBy'] ?? [];

        $quotedTableName = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);

        $timestamp = "";
        $hasPrimaryKey = false;
        foreach ($this->_primaryKeyFields as $key) {
            if (isset($select[$key])) {
                $hasPrimaryKey = true;
                break;
            }
        }

        $hasPrimaryKeyProcessed = false;
        foreach ($this->_fieldsRelated as $relationName) {
            if (!array_key_exists($relationName, $select)) {
                continue;
            }

            if (!$hasPrimaryKeyProcessed && !$hasPrimaryKey) {
                $primaryEntityFields = array_merge($primaryEntityFields, $this->_primaryKeyFields);
                $hasPrimaryKeyProcessed = true;
            }

            $includes[$relationName] = $select[$relationName];

            $relationKeyToSelect = $this->_fieldsRelatedWithKeys[$relationName] ?? null;
            if (!empty($relationKeyToSelect['relationFromFields'])) {
                $fromFields = $relationKeyToSelect['relationFromFields'];
                if (!empty(array_intersect($fromFields, array_keys($this->_fields)))) {
                    $primaryEntityFields = array_merge($primaryEntityFields, $fromFields);
                }
            }
        }

        if (!empty($orderBy)) {
            foreach ($this->_fieldsRelated as $relationName) {
                if (isset($orderBy[$relationName])) {
                    $includeForJoin = array_merge($includeForJoin, [$relationName => true]);
                }
            }
        }

        PPHPUtility::checkIncludes($include, $relatedEntityFields, $includes, $this->_fields, $this->_modelName);
        PPHPUtility::checkFieldsExistWithReferences($select, $relatedEntityFields, $primaryEntityFields, $this->_fieldsRelated, $this->_fields, $this->_modelName, $timestamp);

        if (isset($criteria['cursor']) && is_array($criteria['cursor'])) {
            foreach ($criteria['cursor'] as $field => $value) {
                $select[$field] = ['>=' => $value];
                $fieldQuoted = PPHPUtility::quoteColumnName($this->_dbType, $field);
                $conditions[] = "$fieldQuoted >= :cursor_$field";
                $bindings[":cursor_$field"] = $value;
            }
            if (!isset($select['skip'])) {
                $select['skip'] = 1;
            }
        }

        foreach ($this->_fieldsRelated as $relationName) {
            $field = $this->_fields[$relationName] ?? [];
            if (array_key_exists($relationName, $where) && $field) {
                $relatedClass = "Lib\\Prisma\\Classes\\" . $field['type'];
                $relatedInstance = new $relatedClass($this->_pdo);
                $tableName = PPHPUtility::quoteColumnName($this->_dbType, $relatedInstance->_tableName);
                $relatedFieldKeys = $this->_fieldsRelatedWithKeys[$relationName];

                foreach ($this->_relationConditions as $conditionType) {
                    if (isset($where[$relationName][$conditionType])) {
                        $existsClause = $this->buildRelationCondition(
                            $relationName,
                            $conditionType,
                            $where[$relationName][$conditionType],
                            $field,
                            $bindings
                        );
                        $conditions[] = $existsClause;
                        unset($where[$relationName]);
                        continue 2;
                    }
                }

                if (!empty($relatedFieldKeys['relationFromFields']) && !empty($relatedFieldKeys['relationToFields'])) {
                    $joinConditions = [];

                    foreach ($relatedFieldKeys['relationFromFields'] as $index => $fromField) {
                        $toField = $relatedFieldKeys['relationToFields'][$index] ?? null;
                        if ($toField) {
                            $quotedFromField = PPHPUtility::quoteColumnName($this->_dbType, $fromField);
                            $quotedToField = PPHPUtility::quoteColumnName($this->_dbType, $toField);

                            if (array_key_exists($fromField, $this->_fields)) {
                                $joinConditions[] = "$quotedTableName.$quotedFromField = $tableName.$quotedToField";
                            } else {
                                $joinConditions[] = "$tableName.$quotedFromField = $quotedTableName.$quotedToField";
                            }
                        }
                    }

                    if (!empty($joinConditions)) {
                        $joins[] = "LEFT JOIN $tableName ON " . implode(" AND ", $joinConditions);
                    }

                    if ($where[$relationName] === null) {
                        $relationCondition = [$relatedFieldKeys['relationFromFields'][0] => null];
                    } else if (!empty($where[$relationName])) {
                        $relationCondition = is_array($where[$relationName])
                            ? array_combine($relatedFieldKeys['relationFromFields'], array_values($where[$relationName]))
                            : [$relatedFieldKeys['relationFromFields'][0] => $where[$relationName]];
                    }

                    PPHPUtility::processConditions($relationCondition, $conditions, $bindings, $this->_dbType, $tableName);

                    unset($where[$relationName]);
                } else {
                    throw new Exception("Relation field not properly defined for '$relationName'");
                }
            }
        }

        if (!empty($includeForJoin)) {
            PPHPUtility::buildJoinsRecursively(
                $includeForJoin,
                $quotedTableName,
                $joins,
                $selectFields,
                $this->_pdo,
                $this->_dbType,
                $this
            );
        }

        $primarySelectFields = empty($primaryEntityFields)
            ? array_map(function ($field) use ($quotedTableName) {
                $quotedField = PPHPUtility::quoteColumnName($this->_dbType, $field);
                return "$quotedTableName.$quotedField";
            }, $this->_tableFieldsOnly)
            : array_map(function ($field) use ($quotedTableName) {
                $quotedField = PPHPUtility::quoteColumnName($this->_dbType, $field);
                return "$quotedTableName.$quotedField";
            }, $primaryEntityFields);

        $selectFields = array_merge($primarySelectFields, $selectFields);
        
        $sql = "SELECT $distinct " . implode(', ', $selectFields) . " FROM $quotedTableName";

        if (!empty($joins)) {
            $sql .= " " . implode(' ', $joins);
        }

        $this->processNestedRelationConditions($where, $conditions, $bindings);

        PPHPUtility::processConditions($where, $conditions, $bindings, $this->_dbType, $quotedTableName);

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        PPHPUtility::queryOptions($criteria, $sql, $this->_dbType, $quotedTableName);

        $sql .= " LIMIT 1";

        $stmt = $this->_pdo->prepare($sql);
        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $record = $stmt->fetch();

        if (!$record) {
            return null;
        }

        $record = PPHPUtility::populateIncludedRelations($record, $includes, $this->_fields, $this->_fieldsRelatedWithKeys, $this->_pdo, $this->_dbType);

        if (!empty($select)) {
            foreach (array_keys($record) as $key) {
                if (!isset($select[$key])) {
                    unset($record[$key]);
                }
            }
        }

        if (!empty($omit)) {
            PPHPUtility::checkFieldsExist($omit, $this->_fields, $this->_modelName);

            foreach (array_keys($omit) as $fieldToOmit) {
                unset($record[$fieldToOmit]);
            }
        }

        $record = PPHPUtility::normalizeRowTypes($record, $this->_fields);
        return (object) $record;
    }

    /**
     * Retrieves the first UserRole record that matches specified filter criteria or throws an exception if not found.
     *
     * This method is similar to `findFirst`, but it guarantees that a UserRole record is returned. If no record
     * matches the criteria, it throws an Exception, ensuring that the caller can handle the absence of data
     * appropriately.
     *
     * @param array $criteria Associative array of query parameters, which may include:
     *  - 'where': Filter criteria for searching UserRole records.
     *  - 'orderBy': Specifies the order of records.
     *  - 'select': Fields to include in the result.
     *  - 'include': Related models to include in the results.
     *  - 'take': Limits the number of records returned, useful for limiting results to a single record or a specific number of records.
     *  - 'skip': Skips a number of records, useful in conjunction with 'take' for pagination.
     *  - 'cursor': Cursor-based pagination, specifying the record to start retrieving records from.
     *  - 'distinct': Ensures the query returns only distinct records based on the specified field(s).
     *
     * Returns:
     * @return UserRoleData The UserRoleData record matching the criteria.
     *
     * Throws:
     * @throws Exception if no UserRole record matches the criteria.
     */
    public function findFirstOrThrow(array $criteria = []): object
    {
        $result = $this->findFirst($criteria);

        if ($result === null) {
            throw new Exception("No {$this->_modelName} record found matching the criteria.");
        }

        return $result;
    }

    /**
     * Updates a User in the database.
     *
     * This method updates an existing User record based on the provided filter criteria and
     * update data. It supports updating related records through relations defined in the User model,
     * such as 'userRole', 'product', 'post', and 'Profile'. Additionally, it allows for selective field
     * return and including related models in the response after the update.
     *
     * Workflow:
     * 1. Validates the presence of 'where' and 'data' keys in the input array.
     * 2. Checks for exclusivity between 'select' and 'include' keys, throwing an exception if both are present.
     * 3. Prepares the SQL UPDATE statement based on the provided criteria and data.
     * 4. Executes the update operation within a database transaction to ensure data integrity.
     * 5. Processes any specified relations (e.g., creating related records) as part of the update.
     * 6. Customizes the returned user record based on 'select' or 'include' parameters if specified.
     * 7. Commits the transaction and returns the updated user record, optionally with related data.
     *
     * The method ensures data integrity and consistency throughout the update process by employing
     * transactions. This approach allows for rolling back changes in case of an error, thereby preventing
     * partial updates or data corruption.
     *
     * Parameters:
     * - @param array $data An associative array containing the update criteria and data, which includes:
     *   - 'where': Filter criteria to identify the User to update.
     *   - 'data': The data to update in the User record.
     *   - 'select': Optionally, specifies a subset of fields to return.
     *   - 'include': Optionally, specifies related models to include in the result.
     * 
     * Returns:
     * @return UserRoleData|array|null The updated User record or an empty array if no match is found.
     * 
     * Example Usage:
     * Example 1: Update a UserRole's email and only return their 'id' and 'email' in the response
     * $result = $prisma->userRole->update([
     *   'where' => ['id' => 'someUserId'],
     *   'data' => ['email' => 'new.email@example.com'],
     *   'select' => ['id' => true, 'email' => true]
     * ]);
     * 
     * Example 2: Update a UserRole's username and include their profile information in the response
     * $result = $prisma->userRole->update([
     *   'where' => ['id' => 'someUserId'],
     *   'data' => ['username' => 'newUsername'],
     *   'include' => ['profile' => true]
     * ]);
     * 
     * Throws:
     * @throws Exception if both 'include' and 'select' are used simultaneously, or in case of any error during the update process.
     */
    public function update(array $data): array|object
    {
        if (!isset($data['where'])) {
            throw new Exception("The 'where' key is required in the update UserRole.");
        }

        if (!is_array($data['where'])) {
            throw new Exception("'where' must be an associative array.");
        }

        if (!isset($data['data'])) {
            throw new Exception("The 'data' key is required in the update UserRole.");
        }

        if (!is_array($data['data'])) {
            throw new Exception("'data' must be an associative array.");
        }

        if (isset($data['include']) && isset($data['select'])) {
            throw new LogicException("You cannot use both 'include' and 'select' simultaneously.");
        }

        $acceptedCriteria = ['where', 'data', 'select', 'include', 'omit'];
        PPHPUtility::checkForInvalidKeys($data, $acceptedCriteria, $this->_modelName);
        
        $criteria = $data;
        $where = $data['where'];
        $select = $data['select'] ?? [];
        $include = $data['include'] ?? [];
        $omit = $data['omit'] ?? [];
        $dataToUpdate = $data['data'];
        $updateFields = [];
        $bindings = [];
        $placeholders = [];

        $quotedTableName = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);
        $sql = "UPDATE $quotedTableName SET ";

        PPHPUtility::checkFieldsExist(array_merge($dataToUpdate, $select, $include), $this->_fields, $this->_modelName);

        try {
            $this->_pdo->beginTransaction();

            foreach ($this->_fields as $field) {
                $fieldName = $field['name'];
                $fieldType = $field['type'];
                $isRequired = $field['isRequired'] ?? false;
                $kind = $field['kind'] ?? 'scalar';
                $isList = $field['isList'] ?? false;
                $isUpdatedAt = $field['isUpdatedAt'] ?? false;
                $dbName = $field['dbName'] ?? $fieldName;
                
                if ($isUpdatedAt) {
                    if (!array_key_exists($fieldName, $dataToUpdate) || empty($dataToUpdate[$fieldName])) {
                        $bindings[":$dbName"] = date('Y-m-d H:i:s');
                        $updateFields[] = PPHPUtility::quoteColumnName($this->_dbType, $dbName) . " = :$dbName";
                        $placeholders[] = ":$dbName";
                    } else {
                        $validateMethodName = lcfirst($fieldType);
                        $bindings[":$dbName"] = Validator::$validateMethodName($dataToUpdate[$fieldName]);
                        $updateFields[] = PPHPUtility::quoteColumnName($this->_dbType, $dbName) . " = :$dbName";
                        $placeholders[] = ":$dbName";
                    }
                    continue;
                }

                if ($kind === 'enum' && array_key_exists($fieldName, $dataToUpdate)) {
                    $enumClass = 'Lib\\Prisma\\Classes\\' . $fieldType;
                    $rawValue  = $dataToUpdate[$fieldName];

                    $validated = Validator::enumClass($rawValue, $enumClass);
                    if ($validated === null) {
                        throw new InvalidArgumentException(
                            "Valor inválido para enum '$fieldType' en campo '$fieldName'."
                        );
                    }

                    $bindings[":$dbName"] = $isList
                        ? json_encode($validated)
                        : $validated;

                    $updateFields[] = PPHPUtility::quoteColumnName($this->_dbType, $dbName) . " = :$dbName";
                    continue;
                }

                if (array_key_exists($fieldName, $dataToUpdate) && $kind === 'object') {
                    foreach ($this->_fieldsRelatedWithKeys as $relationName => $relationFields) {
                        if ($fieldName === $relationName) {
                            if ($isRequired) {
                                $validActions = ['connect', 'connectOrCreate', 'create', 'delete', 'deleteMany', 'disconnect', 'set', 'update', 'updateMany', 'upsert'];
                            } else {
                                $validActions = ['connect', 'connectOrCreate', 'create', 'delete', 'disconnect', 'update', 'upsert'];
                            }

                            if ($isRequired) {
                                if (!array_key_exists($relationName, $dataToUpdate)) {
                                    throw new Exception(
                                        "Missing required relation data for field '$fieldName' via relation '$relationName' in model '{$this->_modelName}'. " .
                                            "Expected one of the valid actions: " . implode(', ', ['create', 'connect', 'connectOrCreate'])
                                    );
                                }

                                $relationData = $dataToUpdate[$relationName];
                                if (empty($relationData)) {
                                    throw new Exception(
                                        "Missing required relation data for field '$fieldName' via relation '$relationName' in model '{$this->_modelName}'. " .
                                            "Expected one of the valid actions: " . implode(', ', $validActions)
                                    );
                                }
                                foreach ($relationData as $action => $records) {
                                    if (!in_array($action, $validActions, true)) {
                                        throw new Exception(
                                            "Invalid relation action '$action' for field '$fieldName' via relation '$relationName' in model '{$this->_modelName}'. " .
                                                "Allowed actions: " . implode(', ', $validActions)
                                        );
                                    }
                                }
                            }

                            if (array_key_exists($relationName, $dataToUpdate)) {
                                $actionType = array_keys($dataToUpdate[$relationName])[0];
                                if (!in_array($actionType, $validActions, true)) {
                                    throw new Exception(
                                        "Invalid relation action '$actionType'. Allowed: " . implode(', ', $validActions)
                                    );
                                }

                                $fieldsRelatedWithKeys = $this->_fieldsRelatedWithKeys[$relationName];
                                if (!empty($fieldsRelatedWithKeys['relationFromFields']) || !empty($fieldsRelatedWithKeys['relationToFields'])) {
                                    $fkFields = $fieldsRelatedWithKeys['relationFromFields'];
                                    $isBelongsTo = !empty($fkFields) && array_key_exists($fkFields[0], $this->_fields);

                                    if ($isBelongsTo) {
                                        if ($actionType === 'disconnect' && is_bool($dataToUpdate[$relationName][$actionType])) {
                                            $updateFields[] = PPHPUtility::quoteColumnName($this->_dbType, $fieldsRelatedWithKeys['relationFromFields'][0]) . " = NULL";
                                            unset($dataToUpdate[$relationName]);
                                            continue 2;
                                        }

                                        $this->handleRelatedField($fieldName, $dataToUpdate, $bindings, $updateFields, $placeholders);
                                    }
                                }
                            }

                            continue 2;
                        }
                    }
                }

                if (isset($dataToUpdate[$fieldName]) || !$isRequired) {
                    if (!array_key_exists($fieldName, $dataToUpdate)) continue;
                    if ($kind === 'object') continue;

                    $atomicOp = $this->detectAtomicOperation($dataToUpdate[$fieldName], $fieldName, $field);

                    if ($atomicOp !== null) {
                        $operationSQL = $this->generateAtomicOperationSQL(
                            $dbName,
                            $atomicOp['operation'],
                            $atomicOp['value'],
                            $this->_dbType
                        );
                        $updateFields[] = $operationSQL;
                        $bindings[":atomic_{$dbName}"] = $atomicOp['value'];
                        continue;
                    }

                    $validateMethodName = lcfirst($fieldType);
                    $validatedValue = Validator::$validateMethodName($dataToUpdate[$fieldName]);
                    $rawValue = $dataToUpdate[$fieldName];

                    if ($rawValue === null) {
                        $bindings[":$fieldName"] = null;
                    } else {
                        $validateMethodName = lcfirst($fieldType);
                        $validatedValue = Validator::$validateMethodName($rawValue);

                        if ($fieldType === 'Boolean') {
                            $bindings[":$fieldName"] = $validatedValue ? 1 : 0;
                        } elseif ($validatedValue instanceof BigInteger || $validatedValue instanceof BigDecimal) {
                            $bindings[":$fieldName"] = $validatedValue->__toString();
                        } else {
                            $bindings[":$fieldName"] = $validatedValue;
                        }
                    }

                    $updateFields[] = PPHPUtility::quoteColumnName($this->_dbType, $fieldName) . " = :$fieldName";
                } else {
                    if (array_key_exists($fieldName, $dataToUpdate) && $isRequired) {
                        if ($kind === 'object') continue;
                        $updateFields[] = PPHPUtility::quoteColumnName($this->_dbType, $fieldName) . " = NULL";
                    }
                }
            }

            if (!empty($updateFields)) {
                $sql .= implode(', ', $updateFields);
                $conditions = [];

                $this->processNestedRelationConditions($where, $conditions, $bindings);

                PPHPUtility::processConditions($where, $conditions, $bindings, $this->_dbType, $quotedTableName);

                if (!empty($conditions)) {
                    $sql .= " WHERE " . implode(' AND ', $conditions);
                }

                PPHPUtility::queryOptions($criteria, $sql, $this->_dbType, $quotedTableName);

                $stmt = $this->_pdo->prepare($sql);
                foreach ($bindings as $key => $value) {
                    $stmt->bindValue($key, $value);
                }

                $stmt->execute();

                $updateId = $this->findFirst(['where' => $where])->{$this->_primaryKey};
                if (!$updateId) {
                    throw new Exception("Primary key value not found in where clause.");
                }

                foreach ($this->_fieldsRelatedWithKeys as $fieldRelatedName => $fieldsRelated) {
                    if (!array_key_exists($fieldRelatedName, $dataToUpdate) || !array_key_exists($fieldRelatedName, $this->_fields)) {
                        continue;
                    }

                    $isRequired = $this->_fields[$fieldRelatedName]['isRequired'] ?? false;

                    if ($isRequired) {
                        $validActions = ['connect', 'connectOrCreate', 'create', 'delete', 'deleteMany', 'disconnect', 'set', 'update', 'updateMany', 'upsert'];
                    } else {
                        $validActions = ['connect', 'connectOrCreate', 'create', 'delete', 'disconnect', 'update', 'upsert'];
                    }
                    $relationData  = $dataToUpdate[$fieldRelatedName];

                    foreach ($relationData as $action => $records) {
                        if (!in_array($action, $validActions, true)) {
                            throw new Exception(
                                "Invalid relation action '$action'. Allowed: " . implode(', ', $validActions)
                            );
                        }

                        if (!is_array($records)) {
                            throw new Exception(
                                "Expected an array for '$fieldRelatedName.$action' because it is a relation field."
                            );
                        }

                        if (array_keys($records) !== range(0, count($records) - 1)) {
                            $records = [$records];
                        }

                        $actionReference = [];
                        if (empty($fieldsRelated['relationFromFields']) && empty($fieldsRelated['relationToFields'])) {
                            $relatedFieldType = $this->_fields[$fieldRelatedName]['type'];
                            $nestedCreate = [];
                            foreach ($records as $record) {
                                $nestedCreate[] = [
                                    $relatedFieldType => $record,
                                    $this->_modelName => [$this->_primaryKey => $updateId]
                                ];
                            }

                            $actionReference = [
                                $action => $nestedCreate
                            ];
                        } else {
                            $records = array_map(function ($record) use ($fieldsRelated, $updateId) {
                                foreach ($fieldsRelated['relationFromFields'] as $index => $fromField) {
                                    $toField = $fieldsRelated['relationToFields'][$index];
                                    $record[$fromField] = is_array($updateId) && isset($updateId[$toField])
                                        ? $updateId[$toField]
                                        : $updateId;
                                }
                                return $record;
                            }, $records);

                            $actionReference = [$action => $records];
                        }

                        PPHPUtility::processRelation(
                            $this->_modelName,
                            $fieldRelatedName,
                            $actionReference,
                            $this->_pdo,
                            $this->_dbType,
                            false,
                        );
                    }
                }
            }

            $selectOrInclude = '';
            $selectedFields = [];
            if (!empty($select)) {
                $selectOrInclude = 'select';
                $selectedFields = $select;
            } elseif (!empty($include)) {
                $selectOrInclude = 'include';
                $selectedFields = $include;
            }

            $query = ['where' => $where];

            if (!empty($selectedFields)) {
                $query[$selectOrInclude] = $selectedFields;
            }

            if (!empty($omit)) {
                $query['omit'] = $omit;
            }

            $result = $this->findFirst($query);
            $this->_pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->_pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Bulk update UserRole records with flexible conditions and smart validation.
     *
     * This method lets you update multiple UserRole records at once, based on any conditions you specify.
     * Pass an array with a 'where' key (to filter which records to update) and a 'data' key (fields and new values).
     *
     * - All fields in 'data' are validated for type and constraints.
     * - Only the fields you specify are updated—others remain unchanged.
     * - The update runs inside a transaction: if anything fails, no changes are saved.
     *
     * @param:
     *   - array $data: Must include:
     *       - 'where': Associative array of conditions (e.g., ['status' => 'inactive']).
     *       - 'data': Associative array of fields to update (e.g., ['status' => 'active']).
     *
     * @return:
     *   - object: ['count' => N] where N is the number of records updated.
     *
     * @throws:
     *   - Exception if required keys are missing, if validation fails, or if the database operation fails.
     *
     * Example:
     *   $result = $prisma->userRole->updateMany([
     *     'where' => ['name' => null],
     *     'data'  => ['name' => 'Anonymous']
     *   ]);
     *   $result = (object)['count' => 3]
     *
     * This is perfect for mass updates—like activating UserRole, anonymizing data, or fixing typos—quickly and safely.
     */
    public function updateMany(array $data): object
    {
        if (!isset($data['where']) || !is_array($data['where'])) {
            throw new Exception("'where' must be an associative array in updateMany {$this->_modelName}.");
        }
        if (!isset($data['data'])  || !is_array($data['data'])) {
            throw new Exception("'data' must be an associative array in updateMany {$this->_modelName}.");
        }

        $accepted = ['where', 'data'];
        PPHPUtility::checkForInvalidKeys($data, $accepted, $this->_modelName);

        $where        = $data['where'];
        $dataToUpdate = $data['data'];

        PPHPUtility::checkFieldsExist(
            array_merge($dataToUpdate, $where),
            $this->_fields,
            $this->_modelName
        );

        $quotedTable = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);
        $sql         = "UPDATE {$quotedTable} SET ";

        $updateFields = [];
        $bindings     = [];

        foreach ($this->_fields as $field) {
            $name        = $field['name'];
            $type        = $field['type'];
            $isUpdatedAt = $field['isUpdatedAt'] ?? false;
            $kind  = $field['kind'] ?? 'scalar';
            $isList = $field['isList'] ?? false;

            if ($isUpdatedAt) {
                $bindings[":{$name}"] = date('Y-m-d H:i:s');
                $updateFields[] = PPHPUtility::quoteColumnName($this->_dbType, $name) . " = :{$name}";
                continue;
            }

            if ($kind === 'object') {
                continue;
            }

            if ($kind === 'enum') {
                if (!array_key_exists($name, $dataToUpdate)) {
                    continue;
                }

                $enumClass = 'Lib\\Prisma\\Classes\\' . $type;
                $casted = Validator::enumClass($dataToUpdate[$name], $enumClass);
                if ($casted === null) {
                    throw new InvalidArgumentException(
                        "Valor inválido para enum '$type' en campo '$name'."
                    );
                }

                $bindings[":{$name}"] = $isList
                    ? json_encode($casted)
                    : $casted;

                $updateFields[] =
                    PPHPUtility::quoteColumnName($this->_dbType, $name) . " = :{$name}";
                continue;
            }

            if (!array_key_exists($name, $dataToUpdate)) {
                continue;
            }

            $atomicOp = $this->detectAtomicOperation($dataToUpdate[$name], $name, $field);

            if ($atomicOp !== null) {
                $operationSQL = $this->generateAtomicOperationSQL(
                    $name,
                    $atomicOp['operation'],
                    $atomicOp['value'],
                    $this->_dbType
                );
                $updateFields[] = $operationSQL;
                $bindings[":atomic_{$name}"] = $atomicOp['value'];
                continue;
            }

            $validator      = lcfirst($type);
            $validatedValue = Validator::$validator($dataToUpdate[$name]);

            if ($type === 'Boolean') {
                $validatedValue = $validatedValue ? 1 : 0;
            } elseif (
                $validatedValue instanceof BigInteger ||
                $validatedValue instanceof BigDecimal
            ) {
                $validatedValue = $validatedValue->__toString();
            }

            $bindings[":{$name}"] = $validatedValue;
            $updateFields[] = PPHPUtility::quoteColumnName($this->_dbType, $name) . " = :{$name}";
        }

        if (empty($updateFields)) {
            throw new Exception("No valid scalar fields provided in 'data' for updateMany {$this->_modelName}.");
        }

        $sql .= implode(', ', $updateFields);

        $conditions = [];
        PPHPUtility::processConditions($where, $conditions, $bindings, $this->_dbType, $quotedTable);

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        try {
            $this->_pdo->beginTransaction();

            $stmt = $this->_pdo->prepare($sql);
            foreach ($bindings as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $count = $stmt->rowCount();
            $this->_pdo->commit();

            return (object)['count' => $count];
        } catch (Exception $e) {
            $this->_pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Updates multiple UserRole records and returns the updated records.
     *
     * This method allows you to update multiple UserRole records based on specified conditions
     * and returns the updated records. It supports flexible criteria for selecting which records
     * to update and what data to apply.
     *
     * @param array $data An associative array containing:
     *   - 'where': Conditions to select records to update (required).
     *   - 'data': Fields and values to update (required).
     *   - 'select': Optional. Fields to return in the result.
     *   - 'include': Optional. Related records to include in the result.
     *   - 'omit': Optional. Fields to exclude from the result.
     *
     * @return array An array of updated UserRoleData objects.
     *
     * @throws Exception if 'where' or 'data' is not provided, or if invalid keys are present.
     */
    public function updateManyAndReturn(array $data): array
    {
        if (!isset($data['where']) || !is_array($data['where'])) {
            throw new Exception("'where' must be an associative array in updateManyAndReturn {$this->_modelName}.");
        }

        if (!isset($data['data']) || !is_array($data['data'])) {
            throw new Exception("'data' must be an associative array in updateManyAndReturn {$this->_modelName}.");
        }

        if (!empty($data['include']) && !empty($data['select'])) {
            throw new LogicException("You cannot use both 'include' and 'select' simultaneously.");
        }

        $acceptedCriteria = ['where', 'data', 'select', 'include', 'omit'];
        PPHPUtility::checkForInvalidKeys($data, $acceptedCriteria, $this->_modelName);

        $where = $data['where'];
        $dataToUpdate = $data['data'];
        $select = $data['select'] ?? [];
        $include = $data['include'] ?? [];
        $omit = $data['omit'] ?? [];

        PPHPUtility::checkFieldsExist(
            array_merge($dataToUpdate, $where, $select, $include),
            $this->_fields,
            $this->_modelName
        );

        $primaryKeyField = !$this->_primaryKey && !empty($this->_compositeKeys)
            ? $this->_compositeKeys[0]
            : $this->_primaryKey;

        $quotedTable = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);

        try {
            $this->_pdo->beginTransaction();

            $idsQuery = "SELECT " . PPHPUtility::quoteColumnName($this->_dbType, $primaryKeyField) . " FROM {$quotedTable}";
            $idsConditions = [];
            $idsBindings = [];

            PPHPUtility::processConditions($where, $idsConditions, $idsBindings, $this->_dbType, $quotedTable);

            if (!empty($idsConditions)) {
                $idsQuery .= ' WHERE ' . implode(' AND ', $idsConditions);
            }

            $idsStmt = $this->_pdo->prepare($idsQuery);
            foreach ($idsBindings as $key => $value) {
                $idsStmt->bindValue($key, $value);
            }
            $idsStmt->execute();
            $recordIds = $idsStmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($recordIds)) {
                $this->_pdo->commit();
                return [];
            }

            $sql = "UPDATE {$quotedTable} SET ";
            $updateFields = [];
            $bindings = [];

            foreach ($this->_fields as $field) {
                $name = $field['name'];
                $type = $field['type'];
                $isUpdatedAt = $field['isUpdatedAt'] ?? false;
                $kind = $field['kind'] ?? 'scalar';
                $isList = $field['isList'] ?? false;

                if ($isUpdatedAt) {
                    $bindings[":{$name}"] = date('Y-m-d H:i:s');
                    $updateFields[] = PPHPUtility::quoteColumnName($this->_dbType, $name) . " = :{$name}";
                    continue;
                }

                if ($kind === 'object') {
                    continue;
                }

                if ($kind === 'enum') {
                    if (!array_key_exists($name, $dataToUpdate)) {
                        continue;
                    }

                    $enumClass = 'Lib\\Prisma\\Classes\\' . $type;
                    $casted = Validator::enumClass($dataToUpdate[$name], $enumClass);
                    if ($casted === null) {
                        throw new InvalidArgumentException(
                            "Invalid value for enum '$type' in field '$name'."
                        );
                    }

                    $bindings[":{$name}"] = $isList ? json_encode($casted) : $casted;
                    $updateFields[] = PPHPUtility::quoteColumnName($this->_dbType, $name) . " = :{$name}";
                    continue;
                }

                if (!array_key_exists($name, $dataToUpdate)) {
                    continue;
                }

                $atomicOp = $this->detectAtomicOperation($dataToUpdate[$name], $name, $field);

                if ($atomicOp !== null) {
                    $operationSQL = $this->generateAtomicOperationSQL(
                        $name,
                        $atomicOp['operation'],
                        $atomicOp['value'],
                        $this->_dbType
                    );
                    $updateFields[] = $operationSQL;
                    $bindings[":atomic_{$name}"] = $atomicOp['value'];
                    continue;
                }

                $validator = lcfirst($type);
                if ($type === 'Decimal') {
                    $scale = 30;
                    if (!empty($field['nativeType'][1])) {
                        $scale = intval($field['nativeType'][1][1]);
                    }
                    $validatedValue = Validator::$validator($dataToUpdate[$name], $scale);
                } else {
                    $validatedValue = Validator::$validator($dataToUpdate[$name]);
                }

                if ($type === 'Boolean') {
                    $validatedValue = $validatedValue ? 1 : 0;
                } elseif ($validatedValue instanceof BigInteger || $validatedValue instanceof BigDecimal) {
                    $validatedValue = $validatedValue->__toString();
                }

                $bindings[":{$name}"] = $validatedValue;
                $updateFields[] = PPHPUtility::quoteColumnName($this->_dbType, $name) . " = :{$name}";
            }

            if (empty($updateFields)) {
                throw new Exception("No valid scalar fields provided in 'data' for updateManyAndReturn {$this->_modelName}.");
            }

            $sql .= implode(', ', $updateFields);

            $conditions = [];
            PPHPUtility::processConditions($where, $conditions, $bindings, $this->_dbType, $quotedTable);

            if (!empty($conditions)) {
                $sql .= ' WHERE ' . implode(' AND ', $conditions);
            }

            $stmt = $this->_pdo->prepare($sql);
            foreach ($bindings as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            $fetchQuery = [
                'where' => [$primaryKeyField => ['in' => $recordIds]]
            ];

            if (!empty($select)) {
                $fetchQuery['select'] = $select;
            }

            if (!empty($include)) {
                $fetchQuery['include'] = $include;
            }
            
            if (!empty($omit)) {
                $fetchQuery['omit'] = $omit;
            }

            $results = $this->findMany($fetchQuery);

            $this->_pdo->commit();
            return $results;
        } catch (Exception $e) {
            $this->_pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Deletes a User from the database based on specified criteria.
     *
     * This method enables the deletion of an existing User record through filter criteria
     * defined in an associative array. Before deletion, it verifies the User's existence and
     * optionally returns the User's data pre-deletion. It ensures precise deletion by requiring
     * conditions that uniquely identify the User.
     * 
     * @param array $criteria An associative array containing the filter criteria to locate and delete the User.
     *                        The 'where' key within this array is mandatory and should uniquely identify a single User record.
     *                        Optionally, 'select' or 'include' keys may be provided (but not both) to specify which data to return
     *                        upon successful deletion.
     * @return UserRoleData|array On successful deletion, returns the deleted UserRoleData record.
     *               If the deletion is unsuccessful due to a non-existent UserRoleData or non-unique criteria, returns an
     *               array with 'modelName' and 'cause' keys, indicating the reason for failure.
     * 
     * @example
     * Delete a UserRole by ID and return the deleted User's data as an array
     * $result = $prisma->userRole->delete([
     *   'where' => ['id' => 'someUserId']
     * ]);
     * 
     * @example
     * Delete a UserRole by ID, selecting specific fields to return
     * $result = $prisma->userRole->delete([
     *   'where' => ['id' => 'someUserId'],
     *   'select' => ['name' => true, 'email' => true]
     * ]);
     * 
     * @example
     * Delete a UserRole by ID, including related records in the return value
     * $result = $prisma->userRole->delete([
     *   'where' => ['id' => 'someUserId'],
     *   'include' => ['posts' => true]
     * ]);
     * 
     * @throws Exception if the 'where' key is missing or not an associative array in `$criteria`.
     * @throws Exception if both 'include' and 'select' keys are present in `$criteria`, as they cannot be used simultaneously.
     * @throws Exception if there's an error during the deletion process or if the transaction fails,
     *                   indicating the nature of the error for debugging purposes.
     */
    public function delete(array $criteria): object|array
    {
        if (!isset($criteria['where'])) {
            throw new Exception("The 'where' key is required in the delete User.");
        }

        if (!is_array($criteria['where'])) {
            throw new Exception("'where' must be an associative array.");
        }

        if (isset($criteria['include']) && isset($criteria['select'])) {
            throw new LogicException("You cannot use both 'include' and 'select' simultaneously.");
        }

        $acceptedCriteria = ['where', 'select', 'include', 'omit'];
        PPHPUtility::checkForInvalidKeys($criteria, $acceptedCriteria, $this->_modelName);

        $where = $criteria['where'];
        $select = $criteria['select'] ?? [];
        $include = $criteria['include'] ?? [];
        $omit = $criteria['omit'] ?? [];
        $whereClauses = [];
        $bindings = [];

        $whereHasUniqueKey = false;
        foreach ($this->_primaryKeyAndUniqueFields as $key) {
            if (array_key_exists($key, $where)) {
                $whereHasUniqueKey = true;
                break;
            }
        }

        if (!$whereHasUniqueKey) {
            throw new Exception("No valid 'where' conditions provided for finding a unique record in $this->_modelName.");
        }

        try {
            $this->_pdo->beginTransaction();

            $quotedTableName = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);

            PPHPUtility::processConditions($where, $whereClauses, $bindings, $this->_dbType, $quotedTableName);

            $sql = "DELETE FROM $quotedTableName WHERE ";
            $sql .= implode(' AND ', $whereClauses);

            $stmt = $this->_pdo->prepare($sql);
            foreach ($bindings as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $selectOrInclude = '';
            $selectedFields = [];
            if (!empty($select)) {
                $selectOrInclude = 'select';
                $selectedFields = $select;
            } elseif (!empty($include)) {
                $selectOrInclude = 'include';
                $selectedFields = $include;
            }

            $query = ['where' => $where];

            if (!empty($selectedFields)) {
                $query[$selectOrInclude] = $selectedFields;
            }

            if (!empty($omit)) {
                $query['omit'] = $omit;
            }

            $deletedRow = $this->findFirst($query);

            $stmt->execute();
            $affectedRows = $stmt->rowCount();
            $this->_pdo->commit();

            return $affectedRows ? $deletedRow : ['modelName' => 'UserRole', 'cause' => 'Record to delete does not exist.'];
        } catch (Exception $e) {
            $this->_pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Performs an Upsert Operation on a UserRole Record
     *
     * Implements an "upsert" operation for UserRole records. This method checks for the existence of a UserRole based on
     * specified 'where' conditions. If the UserRole exists, it updates the UserRole with the provided 'update' data;
     * if the UserRole does not exist, it creates a new UserRole with the 'create' data. This operation is atomic, ensuring
     * data integrity through transaction management. Additionally, it allows for selective field return through
     * 'select' or related records inclusion with 'include'.
     *
     * @params:
     * - array $data: Contains keys for operation configuration:
     *   - 'where': Conditions to find an existing User.
     *   - 'create': Data for creating a new User if no existing match is found.
     *   - 'update': Data for updating an existing User.
     *   - 'select': Optional. Specifies fields to return in the result, reducing payload size.
     *   - 'include': Optional. Specifies related records to include in the result, expanding payload details.
     * - string $format: Determines the return format ('array' or 'object'), allowing flexibility in handling the response.
     * 
     * @return UserRoleData Returns the created or updated User record.
     * 
     * Exceptions:
     * @throws an Exception if essential keys ('where', 'create', or 'update') are missing from the input $data array, or
     *   if an invalid criteria key is provided.
     * 
     * Example Usage:
     * $result = $prisma->userRole->upsert([
     *   'where' => ['email' => 'user@example.com'],
     *   'create' => ['name' => 'New User', 'email' => 'user@example.com', 'password' => 'password'],
     *   'update' => ['name' => 'Updated User Name'],
     *   'select' => ['name', 'email']
     * ]);
     * 
     * This method streamlines data management by allowing for conditional creation or update of User records within a single,
     * atomic operation. It offers enhanced flexibility and efficiency, particularly useful in scenarios where the presence
     * of a record dictates the nature of the transaction, and detailed or minimalistic data retrieval is desired post-operation.
     */
    public function upsert(array $data): object
    {
        if (!isset($data['where']) || !isset($data['create']) || !isset($data['update'])) {
            throw new Exception("Missing criteria keys. 'where', 'create', and 'update' must be provided.");
        }

        if (isset($data['include']) && isset($data['select'])) {
            throw new Exception("You can't use both 'include' and 'select' at the same time.");
        }

        $acceptedCriteria = ['where', 'create', 'update', 'select', 'include', 'omit'];
        PPHPUtility::checkForInvalidKeys($data, $acceptedCriteria, $this->_modelName);

        try {
            $this->_pdo->beginTransaction();

            $where = $data['where'];
            $create = $data['create'];
            $update = $data['update'];
            $select = $data['select'] ?? [];
            $include = $data['include'] ?? [];
            $omit = $data['omit'] ?? [];

            $existingRecord = $this->findUnique(['where' => $where]);

            $selectOrInclude = '';
            if (!empty($select)) {
                $selectOrInclude = 'select';
            } elseif (!empty($include)) {
                $selectOrInclude = 'include';
            }
            $selectedFields = array_merge($select, $include);

            $result = [];
            if ($existingRecord) {
                $dataToUpdate = [
                    'where' => $where,
                    'data' => $update,
                ];
                
                if (!empty($selectedFields)) {
                    $dataToUpdate[$selectOrInclude] = $selectedFields;
                }
                
                if (!empty($omit)) {
                    $dataToUpdate['omit'] = $omit;
                }
                
                $result = $this->update($dataToUpdate);
                $this->_pdo->commit();
            } else {
                $dataToCreate = [
                    'data' => $create,
                ];
                
                if (!empty($selectedFields)) {
                    $dataToCreate[$selectOrInclude] = $selectedFields;
                }
                
                if (!empty($omit)) {
                    $dataToCreate['omit'] = $omit;
                }
                
                $result = $this->create($dataToCreate);
                $this->_pdo->commit();
            }
            return $result;
        } catch (Exception $e) {
            $this->_pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Groups records in the 'UserRole' table based on specified criteria.
     * 
     * This function groups records in the 'UserRole' table according to the fields specified 
     * in the criteria array. The grouped data is returned as an array or an object, depending on the format specified.
     * 
     * @param array $criteria Array specifying the fields to group by. The 'by' key must be present in this array, 
     *                        and its value can be a single field or an array of fields.
     * @return array   An array containing the grouped data.
     * @throws Exception     Throws an exception if the 'by' field is not provided in the criteria array.
     */
    public function groupBy(array $criteria): array
    {
        $acceptedCriteria = [
            'by',
            'where',
            'having',
            'orderBy',
            'skip',
            'take',
            '_count',
            '_avg',
            '_sum',
            '_min',
            '_max'
        ];
        PPHPUtility::checkForInvalidKeys($criteria, $acceptedCriteria, $this->_modelName);

        $byRaw  = $criteria['by']    ?? null;
        $where  = $criteria['where'] ?? [];
        $having = $criteria['having'] ?? [];

        if (!$byRaw || !is_array($byRaw)) {
            throw new Exception("'by' must be a non-empty array.");
        }

        $by = array_filter(array_map('trim', $byRaw), fn($f) => $f !== '');
        if ($by === []) {
            throw new Exception("'by' cannot contain empty values.");
        }
        foreach ($by as $field) {
            if (!isset($this->_fields[$field])) {
                throw new Exception("Field '$field' does not exist in {$this->_modelName}.");
            }
        }

        $qt = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);

        $selectParts = array_map(
            fn($f) => $qt . '.' . PPHPUtility::quoteColumnName($this->_dbType, $f),
            $by
        );

        $aggMap = [
            '_count' => 'COUNT',
            '_avg'   => 'AVG',
            '_sum'   => 'SUM',
            '_min'   => 'MIN',
            '_max'   => 'MAX',
        ];

        foreach ($aggMap as $critKey => $sqlFunc) {
            if (!array_key_exists($critKey, $criteria)) continue;

            if ($critKey === '_count' && $criteria['_count'] === true) {
                $selectParts[] = "COUNT(*) AS " . PPHPUtility::quoteColumnName($this->_dbType, 'count__all');
                continue;
            }

            $req = $criteria[$critKey];
            if (!is_array($req)) {
                throw new Exception("'$critKey' must be an array (or 'true' for _count).");
            }

            foreach ($req as $field => $enabled) {
                if (!$enabled) continue;

                if ($critKey === '_count' && $field === '_all') {
                    $selectParts[] = "COUNT(*) AS " . PPHPUtility::quoteColumnName($this->_dbType, 'count__all');
                    continue;
                }

                if (!isset($this->_fields[$field])) {
                    throw new Exception("Field '$field' does not exist in {$this->_modelName}.");
                }

                $qf    = PPHPUtility::quoteColumnName($this->_dbType, $field);
                $alias = strtolower(substr($critKey, 1)) . '_' . $field;
                $selectParts[] = "$sqlFunc($qt.$qf) AS " . PPHPUtility::quoteColumnName($this->_dbType, $alias);
            }
        }

        $sql = 'SELECT ' . implode(', ', $selectParts) . " FROM $qt";

        $conditions = [];
        $bindings   = [];

        foreach ($this->_fieldsRelated as $relationName) {
            $field = $this->_fields[$relationName] ?? [];
            if (array_key_exists($relationName, $where) && $field) {
                foreach ($this->_relationConditions as $conditionType) {
                    if (isset($where[$relationName][$conditionType])) {
                        $existsClause = $this->buildRelationCondition(
                            $relationName,
                            $conditionType,
                            $where[$relationName][$conditionType],
                            $field,
                            $bindings
                        );
                        $conditions[] = $existsClause;
                        unset($where[$relationName]);
                        continue 2;
                    }
                }

                $relatedFieldKeys = $this->_fieldsRelatedWithKeys[$relationName];
                if (!empty($relatedFieldKeys['relationFromFields']) && !empty($relatedFieldKeys['relationToFields'])) {
                    if ($where[$relationName] === null) {
                        $relationCondition = [$relatedFieldKeys['relationFromFields'][0] => null];
                    } else if (!empty($where[$relationName])) {
                        $relationCondition = is_array($where[$relationName])
                            ? array_combine($relatedFieldKeys['relationFromFields'], array_values($where[$relationName]))
                            : [$relatedFieldKeys['relationFromFields'][0] => $where[$relationName]];
                    }

                    PPHPUtility::processConditions($relationCondition, $conditions, $bindings, $this->_dbType, $qt);
                    unset($where[$relationName]);
                } else {
                    throw new Exception("Relation field not properly defined for '$relationName'");
                }
            }
        }

        PPHPUtility::processConditions($where, $conditions, $bindings, $this->_dbType, $qt);
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' GROUP BY ' . implode(
            ', ',
            array_map(fn($f) => $qt . '.' . PPHPUtility::quoteColumnName($this->_dbType, $f), $by)
        );

        $sql .= PPHPUtility::buildHavingClause($having, $aggMap, $this->_dbType, $bindings);

        PPHPUtility::queryOptions($criteria, $sql, $this->_dbType, $qt, false);

        $stmt = $this->_pdo->prepare($sql);
        foreach ($bindings as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $castAgg = function (string $critKey, ?string $fieldName, $value) {
            if ($value === null) return null;
            if ($critKey === '_count') return (int)$value;

            $type = $fieldName && isset($this->_fields[$fieldName]['type'])
                ? $this->_fields[$fieldName]['type']
                : null;

            switch ($critKey) {
                case '_sum':
                    if ($type === 'Int')     return (int)$value;
                    if ($type === 'BigInt')  return (string)$value;
                    if ($type === 'Decimal') return (string)$value;
                    return is_numeric($value) ? +$value : $value;

                case '_avg':
                    if ($type === 'Decimal' || $type === 'BigInt') {
                        return (string)$value;
                    }
                    return is_numeric($value) ? (float)$value : $value;

                case '_min':
                case '_max':
                    if ($type === 'Int')      return (int)$value;
                    elseif ($type === 'BigInt')   return (string)$value;
                    elseif ($type === 'Decimal')  return (string)$value;
                    elseif ($type === 'Boolean')  return PPHPUtility::toBool($value);
                    elseif ($type === 'DateTime') return Validator::dateTime($value);
                    else                          return $value;

                default:
                    return $value;
            }
        };

        $castGroupKey = function (string $field, $value) {
            $type = $this->_fields[$field]['type'] ?? null;
            if ($value === null) return null;

            return match ($type) {
                'Boolean'  => PPHPUtility::toBool($value),
                'Int'      => (int)$value,
                'BigInt'   => (string)$value,
                'Decimal'  => (string)$value,
                'DateTime' => Validator::dateTime($value),
                default    => $value,
            };
        };

        $out = [];
        foreach ($rows as $row) {
            $obj = [];

            foreach ($by as $gField) {
                $obj[$gField] = $castGroupKey($gField, $row[$gField] ?? null);
            }

            foreach ($aggMap as $critKey => $_) {
                if (!array_key_exists($critKey, $criteria)) continue;

                if ($critKey === '_count' && $criteria['_count'] === true) {
                    $obj['_count'] = (object) ['_all' => (int)($row['count__all'] ?? 0)];
                    continue;
                }

                $req = $criteria[$critKey];
                if (!is_array($req)) continue;

                $aggObj = [];

                if ($critKey === '_count' && !empty($req['_all'])) {
                    $aggObj['_all'] = (int)($row['count__all'] ?? 0);
                }

                foreach ($req as $field => $enabled) {
                    if (!$enabled || $field === '_all') continue;

                    $alias = strtolower(substr($critKey, 1)) . '_' . $field;
                    $aggObj[$field] = $castAgg($critKey, $field, $row[$alias] ?? null);
                }

                if ($aggObj !== []) {
                    $obj[$critKey] = (object)$aggObj;
                }
            }

            $out[] = (object)$obj;
        }

        return $out;
    }

    /**
     * Dynamically deletes multiple UserRole records based on flexible criteria.
     *
     * This method enables bulk deletion of UserRole records by specifying any combination of conditions in the 'where' key.
     * - Accepts any valid UserRole field(s) as filter criteria.
     * - Returns an object with the number of records deleted: (object)['count' => N]
     * - Runs inside a transaction for safety.
     * - Throws clear, user-friendly errors for invalid input.
     *
     * Example:
     *   $result = $prisma->userRole->deleteMany([
     *     'where' => ['email' => 'test@example.com']
     *   ]);
     *   $result = (object)['count' => 1]
     *
     * @param array $criteria Must include a 'where' key with an associative array of conditions.
     * @return object ['count' => N] where N is the number of records deleted.
     * @throws Exception for missing/invalid input or database errors.
     */
    public function deleteMany(array $criteria): object
    {
        if (isset($criteria['where']) && !is_array($criteria['where'])) {
            throw new Exception("'where' must be an associative array.");
        }

        $acceptedCriteria = ['where'];
        PPHPUtility::checkForInvalidKeys($criteria, $acceptedCriteria, $this->_modelName);

        try {
            $this->_pdo->beginTransaction();

            $quotedTableName = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);
            $sql = "DELETE FROM $quotedTableName";
            $bindings = [];

            if (isset($criteria['where']) && !empty($criteria['where'])) {
                $where = $criteria['where'];
                $whereClauses = [];
                PPHPUtility::processConditions($where, $whereClauses, $bindings, $this->_dbType, $quotedTableName);
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }

            $stmt = $this->_pdo->prepare($sql);
            foreach ($bindings as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $affected = $stmt->rowCount();
            $this->_pdo->commit();

            return (object)['count' => $affected];
        } catch (Exception $e) {
            $this->_pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Counts the number of records in the 'VersionLog' table based on provided criteria.
     * 
     * This method counts records that match the specified criteria. The criteria should
     * contain key-value pairs where keys are the names of the fields and values are the
     * conditions to match.
     * 
     * @param array $criteria Associative array of criteria for selecting the records to be counted.
     * @return int|array|UserRoleData The number of records that match the criteria or detailed count per field.
     * @throws Exception Throws an exception if the database operation fails.
     *
     * Example Usage:
     * $prisma = new Prisma();
     * $countCriteria = ['status' => 'active'];
     * $result = $prisma->userRole->count($countCriteria);
     */
    public function count(array $criteria = []): int|array|object
    {
        if (!empty($criteria)) {
            $acceptedCriteria = ['cursor', 'orderBy', 'select', 'skip', 'take', 'where'];
            PPHPUtility::checkForInvalidKeys($criteria, $acceptedCriteria, $this->_modelName);
        }

        $where  = $criteria['where']  ?? [];
        $select = $criteria['select'] ?? [];

        if (!empty($select)) {
            PPHPUtility::checkFieldsExist($select, $this->_fields, $this->_modelName);
        }

        $qt = PPHPUtility::quoteColumnName($this->_dbType, $this->_tableName);

        $conditions = [];
        $bindings   = [];

        if (isset($criteria['cursor']) && is_array($criteria['cursor'])) {
            foreach ($criteria['cursor'] as $field => $value) {
                $qf = PPHPUtility::quoteColumnName($this->_dbType, $field);
                $conditions[] = "$qt.$qf >= :cursor_$field";
                $bindings[":cursor_$field"] = $value;
            }
        }

        foreach ($this->_fieldsRelated as $relationName) {
            $field = $this->_fields[$relationName] ?? null;
            if (!$field) {
                continue;
            }

            if (!array_key_exists($relationName, $where)) {
                continue;
            }

            foreach ($this->_relationConditions as $conditionType) {
                if (isset($where[$relationName]) && is_array($where[$relationName]) && array_key_exists($conditionType, $where[$relationName])) {
                    $existsClause = $this->buildRelationCondition(
                        $relationName,
                        $conditionType,
                        $where[$relationName][$conditionType],
                        $field,
                        $bindings
                    );
                    $conditions[] = $existsClause;

                    unset($where[$relationName]);
                    continue 2;
                }
            }

            $relatedFieldKeys = $this->_fieldsRelatedWithKeys[$relationName] ?? ['relationFromFields' => [], 'relationToFields' => []];

            if (!empty($relatedFieldKeys['relationFromFields']) && !empty($relatedFieldKeys['relationToFields'])) {
                if ($where[$relationName] === null) {
                    $fromField = $relatedFieldKeys['relationFromFields'][0];
                    if (!isset($this->_fields[$fromField])) {
                        unset($where[$relationName]);
                    } else {
                        $relationCondition = [$fromField => null];
                        PPHPUtility::processConditions($relationCondition, $conditions, $bindings, $this->_dbType, $qt);
                        unset($where[$relationName]);
                    }
                } else if (!empty($where[$relationName])) {
                    $fromField = $relatedFieldKeys['relationFromFields'][0];

                    if (!isset($this->_fields[$fromField])) {
                        unset($where[$relationName]);
                    } else {
                        $relationCondition = is_array($where[$relationName])
                            ? array_combine($relatedFieldKeys['relationFromFields'], array_values($where[$relationName]))
                            : [$fromField => $where[$relationName]];

                        PPHPUtility::processConditions($relationCondition, $conditions, $bindings, $this->_dbType, $qt);
                        unset($where[$relationName]);
                    }
                } else {
                    unset($where[$relationName]);
                }
            } else {
                throw new Exception("Relation field not properly defined for '$relationName'");
            }
        }

        $this->processNestedRelationConditions($where, $conditions, $bindings);

        foreach (['AND', 'OR'] as $op) {
            if (isset($where[$op]) && is_array($where[$op])) {
                $where[$op] = array_values($where[$op]);
                if (empty($where[$op])) {
                    unset($where[$op]);
                }
            }
        }

        if (!empty($where)) {
            PPHPUtility::processConditions($where, $conditions, $bindings, $this->_dbType, $qt);
        }

        $sub = "SELECT * FROM $qt";
        if (!empty($conditions)) {
            $sub .= ' WHERE ' . implode(' AND ', $conditions);
        }

        PPHPUtility::queryOptions($criteria, $sub, $this->_dbType, $qt, false);

        $subAlias = 'sub';

        if (empty($select)) {
            $sql = "SELECT COUNT(*) AS total FROM ($sub) AS $subAlias";
            $stmt = $this->_pdo->prepare($sql);
            foreach ($bindings as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->execute();
            $row = $stmt->fetch();
            return (int)($row['total'] ?? 0);
        }

        $parts = [];
        foreach (array_keys($select) as $fieldName) {
            if (!isset($this->_fields[$fieldName])) {
                throw new Exception("Field '$fieldName' does not exist in {$this->_modelName}.");
            }

            $qf = PPHPUtility::quoteColumnName($this->_dbType, $fieldName);
            $alias = 'count_' . $fieldName;
            $aliasQuoted = PPHPUtility::quoteColumnName($this->_dbType, $alias);

            $parts[] = "COUNT($subAlias.$qf) AS $aliasQuoted";
        }

        $sql = 'SELECT ' . implode(', ', $parts) . " FROM ($sub) AS $subAlias";

        $stmt = $this->_pdo->prepare($sql);
        foreach ($bindings as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $row = $stmt->fetch() ?: [];

        $out = [];
        foreach (array_keys($select) as $fieldName) {
            $out[$fieldName] = (int)($row['count_' . $fieldName] ?? 0);
        }

        return (object)$out;
    }
}
