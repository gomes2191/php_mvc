<?php

namespace App\Core\Database;

use App\Core\App;
use PDO;
use PDOException;
use PDOStatement;
use Exception;

class QueryBuilder
{
    /**
     * Instância do PDO.
     * @var PDO
     */
    protected $pdo;

    /**
     * Nome da classe a qual o Modelo estará vinculado.
     * @var
     */
    protected $class_name;

    /**
     * Consulta SQL atual.
     * @var
     */
    protected $sql;

    /**
     * Construtor da classe QueryBuilder, simplesmente inicializa um novo objeto PDO.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Retorna a instância do PDO.
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Retorna a última consulta SQL definida.
     *
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * Define o nome de classe para vincular o Modelo.
     * @param mixed $class_name
     * @return QueryBuilder
     */
    public function setClassName($class_name): QueryBuilder
    {
        $this->class_name = $class_name;
        return $this;
    }

    /**
     * Seleciona todas as linhas de uma tabela em um BD.
     * @param string $table
     * @param string $limit
     * @param string $offset
     * @return array|false
     * @throws Exception
     */
    public function selectAll(string $table, $limit = "", $offset = "")
    {
        return $this->select($table, "*", $limit, $offset);
    }

    /**
     * Seleciona linhas de uma tabela em um BD onde uma ou mais condições são combinadas.
     * @param string $table
     * @param $where
     * @param string $limit
     * @param string $offset
     * @return array|false
     * @throws Exception
     */
    public function selectAllWhere(string $table, $where, $limit = "", $offset = "")
    {
        return $this->selectWhere($table, "*", $where, $limit, $offset);
    }

    /**
     * Retorna o número de linhas em uma tabela.
     * @param string $table
     * @return  int|bool
     * @throws Exception
     */
    public function count(string $table)
    {
        $this->sql = "SELECT COUNT(*) FROM {$table}";
        try {
            $statement = $this->pdo->prepare($this->sql);
            $statement->execute();
            return $statement->fetchColumn();
        } catch (PDOException $e) {
            $this->handlePDOException($e);
        }
        return false;
    }

    /**
     * Retorna o número de linhas em uma tabela onde uma ou mais condições são combinadas.
     * @param string $table
     * @param $where
     * @param string $columns
     * @return int|bool
     * @throws Exception
     */
    public function countWhere(string $table, $where)
    {
        $where = $this->prepareWhere($where);
        $mapped_wheres = $this->prepareMappedWheres($where);
        $where = array_column($where, 3);
        $this->sql = "SELECT COUNT(*) FROM {$table} WHERE {$mapped_wheres}";
        try {
            $statement = $this->pdo->prepare($this->sql);
            $statement->execute($where);
            return $statement->fetchColumn();
        } catch (PDOException $e) {
            $this->handlePDOException($e);
        }
        return false;
    }

    /**
     * Seleciona linhas de uma tabela em um BD.
     * @param string $table
     * @param string $columns
     * @param string $limit
     * @param string $offset
     * @return array|false
     * @throws Exception
     */
    public function select(string $table, string $columns, $limit = "", $offset = "")
    {
        $limit = $this->prepareLimit($limit);
        $offset = $this->prepareOffset($offset);
        $this->sql = "SELECT {$columns} FROM {$table} {$limit} {$offset}";
        try {
            $statement = $this->pdo->prepare($this->sql);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_CLASS, $this->class_name ?: "stdClass");
        } catch (PDOException $e) {
            $this->handlePDOException($e);
        }
        return false;
    }

    /**
     * Seleciona linhas de uma tabela em um BD onde uma ou mais condições são combinadas.
     * @param string $table
     * @param string $columns
     * @param $where
     * @param string $limit
     * @param string $offset
     * @return array|false
     * @throws Exception
     */
    public function selectWhere(string $table, string $columns, $where, $limit = "", $offset = "")
    {
        $limit = $this->prepareLimit($limit);
        $offset = $this->prepareOffset($offset);
        $where = $this->prepareWhere($where);
        $mapped_wheres = $this->prepareMappedWheres($where);
        $where = array_column($where, 3);
        $this->sql = "SELECT {$columns} FROM {$table} WHERE {$mapped_wheres} {$limit} {$offset}";
        try {
            $statement = $this->pdo->prepare($this->sql);
            $statement->execute($where);
            return $statement->fetchAll(PDO::FETCH_CLASS, $this->class_name ?: "stdClass");
        } catch (PDOException $e) {
            $this->handlePDOException($e);
        }
        return false;
    }

    /**
     * Exclui linhas de uma tabela em um BD.
     * @param string $table
     * @param string $limit
     * @return int
     * @throws Exception
     */
    public function delete(string $table, $limit = ""): int
    {
        $limit = $this->prepareLimit($limit);
        $this->sql = "DELETE FROM {$table} {$limit}";
        try {
            $statement = $this->pdo->prepare($this->sql);
            $statement->execute();
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->handlePDOException($e);
        }
        return 0;
    }


    /**
     * Exclui linhas de uma tabela em um BD onde uma ou mais condições são combinadas.
     * @param string $table
     * @param $where
     * @param string $limit
     * @return int
     * @throws Exception
     */
    public function deleteWhere(string $table, $where, $limit = ""): int
    {
        $limit = $this->prepareLimit($limit);
        $where = $this->prepareWhere($where);
        $mapped_wheres = $this->prepareMappedWheres($where);
        $where = array_column($where, 3);
        $this->sql = "DELETE FROM {$table} WHERE {$mapped_wheres} {$limit}";
        try {
            $statement = $this->pdo->prepare($this->sql);
            $statement->execute($where);
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->handlePDOException($e);
        }
        return 0;
    }

    /**
     * Insere dados em uma tabela em um BD.
     * @param string $table
     * @param $parameters
     * @return string
     * @throws Exception
     */
    public function insert(string $table, $parameters): string
    {
        $names = $this->prepareCommaSeparatedColumnNames($parameters);
        $values = $this->prepareCommaSeparatedColumnValues($parameters);
        $this->sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            $names,
            $values
        );
        try {
            $statement = $this->pdo->prepare($this->sql);
            $statement->execute($parameters);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->handlePDOException($e);
        }
        return "";
    }

    /**
     * Atualiza os dados em uma tabela em um BD.
     * @param string $table
     * @param $parameters
     * @param string $limit
     * @return int
     * @throws Exception
     */
    public function update(string $table, $parameters, $limit = ""): int
    {
        $limit = $this->prepareLimit($limit);
        $set = $this->prepareNamed($parameters);
        $this->sql = sprintf(
            'UPDATE %s SET %s %s',
            $table,
            $set,
            $limit
        );
        try {
            $statement = $this->pdo->prepare($this->sql);
            $statement->execute($parameters);
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->handlePDOException($e);
        }
        return 0;
    }

    /**
     * Atualiza os dados em uma tabela em um BD onde uma ou mais condições são combinadas.
     * @param string $table
     * @param $parameters
     * @param $where
     * @param string $limit
     * @return int
     * @throws Exception
     */
    public function updateWhere(string $table, $parameters, $where, $limit = ""): int
    {
        $limit = $this->prepareLimit($limit);
        $set = $this->prepareUnnamed($parameters);
        $parameters = $this->prepareParameters($parameters);
        $where = $this->prepareWhere($where);
        $mapped_wheres = $this->prepareMappedWheres($where);
        $where = array_column($where, 3);
        $this->sql = sprintf(
            'UPDATE %s SET %s WHERE %s %s',
            $table,
            $set,
            $mapped_wheres,
            $limit
        );
        try {
            $statement = $this->pdo->prepare($this->sql);
            $statement->execute(array_merge($parameters, $where));
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->handlePDOException($e);
        }
        return 0;
    }

    /**
     * Seleciona todas as linhas de uma tabela em um BD.
     * @param string $table
     * @return array|int
     * @throws Exception
     */
    public function describe(string $table)
    {
        $this->sql = "DESCRIBE {$table}";
        try {
            $statement = $this->pdo->prepare($this->sql);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_CLASS, $this->class_name ?: "stdClass");
        } catch (PDOException $e) {
            $this->handlePDOException($e);
        }
        return 0;
    }

    /**
     * Executa um SQL bruto contra um BD.
     * @param string $sql
     * @param array $parameters
     * @return array|int
     * @throws Exception
     */
    public function raw(string $sql, array $parameters = [])
    {
        try {
            $this->sql = $sql;
            $statement = $this->pdo->prepare($sql);
            $statement->execute($parameters);
            $output = $statement->rowCount();
            if (stripos($sql, "SELECT") === 0) {
                $output = $statement->fetchAll(PDO::FETCH_CLASS, $this->class_name ?: "stdClass");
            }
            return $output;
        } catch (PDOException $e) {
            $this->handlePDOException($e);
        }
        return 0;
    }

    /**
     * Prepara o conjunto de cláusulas para o query builder.
     * @param $where
     * @return mixed
     */
    private function prepareWhere($where)
    {
        $array = $where;
        foreach ($where as $key => $value) {
            if (count($value) < 4) {
                array_unshift($array[$key], 0);
            }
        }
        return $array;
    }

    /**
     * Prepara a declaração de limite para o query builder.
     * @param $limit
     * @return string
     */
    private function prepareLimit($limit): string
    {
        return (!empty($limit) ? " LIMIT " . $limit : "");
    }

    /**
     * Prepara a instrução offset para o query builder.
     * @param $offset
     * @return string
     */
    private function prepareOffset($offset): string
    {
        return (!empty($offset) ? " OFFSET " . $offset : "");
    }

    /**
     * Prepara os nomes separados por vírgulas para o query builder.
     * @param $parameters
     * @return string
     */
    private function prepareCommaSeparatedColumnNames($parameters): string
    {
        return implode(', ', array_keys($parameters));
    }

    /**
     * Prepara os valores separados por vírgulas para o query builder.
     * @param $parameters
     * @return string
     */
    private function prepareCommaSeparatedColumnValues($parameters): string
    {
        return ':' . implode(', :', array_keys($parameters));
    }

    /**
     * Prepara os wheres mapeados.
     * @param $where
     * @return string
     */
    private function prepareMappedWheres($where): string
    {
        $mapped_wheres = '';
        foreach ($where as $clause) {
            $modifier = $mapped_wheres === '' ? '' : $clause[0];
            $mapped_wheres .= " {$modifier} {$clause[1]} {$clause[2]} ?";
        }
        return $mapped_wheres;
    }

    /**
     * Prepara as colunas sem nome.
     * @param $parameters
     * @return string
     */
    private function prepareUnnamed($parameters): string
    {
        return implode(', ', array_map(
            static function ($property) {
                return "{$property} = ?";
            },
            array_keys($parameters)
        ));
    }

    /**
     * Prepara as colunas nomeadas..
     * @param $parameters
     * @return string
     */
    private function prepareNamed($parameters): string
    {
        return implode(', ', array_map(
            static function ($property) {
                return "{$property} = :{$property}";
            },
            array_keys($parameters)
        ));
    }

    /**
     * Prepara os parâmetros com teclas numéricas.
     * @param $parameters
     * @param int $counter
     * @return mixed
     */
    private function prepareParameters($parameters, $counter = 1)
    {
        foreach ($array = $parameters as $key => $value) {
            unset($parameters[$key]);
            $parameters[$counter] = $value;
            $counter++;
        }
        return $parameters;
    }

    /**
     * Vincula valores de uma matriz ao PDOStatement.
     * @param PDOStatement $PDOStatement
     * @param $array
     * @param int $counter
     */
    private function prepareBindings(PDOStatement $PDOStatement, $array, $counter = 1): void
    {
        foreach ($array as $key => $value) {
            $PDOStatement->bindParam($counter, $value);
            $counter++;
        }
    }

    /**
     * Trata as exceções do PDO.
     * @param PDOException $e
     * @return mixed
     * @throws Exception
     */
    private function handlePDOException(PDOException $e)
    {
        App::logError('There was a PDO Exception. Details: ' . $e);
        if (App::get('config')['options']['debug']) {
            return view('error', ['error' => $e->getMessage()]);
        }
        return view('error');
    }
}