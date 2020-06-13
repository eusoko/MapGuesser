<?php namespace MapGuesser\Session;

use DateTime;
use MapGuesser\Database\Query\Modify;
use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Database\IResultSet;
use SessionHandlerInterface;
use SessionIdInterface;
use SessionUpdateTimestampHandlerInterface;

class DatabaseSessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface
{
    private bool $exists = false;

    private bool $written = false;

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string
    {
        $select = new Select(\Container::$dbConnection, 'sessions');
        $select->columns(['data']);
        $select->whereId($id);

        $result = $select->execute()->fetch(IResultSet::FETCH_ASSOC);

        if ($result === null) {
            return '';
        }

        $this->exists = true;

        return $result['data'];
    }

    public function write($id, $data): bool
    {
        $modify = new Modify(\Container::$dbConnection, 'sessions');

        if ($this->exists) {
            $modify->setId($id);
        } else {
            $modify->setExternalId($id);
        }

        $modify->set('data', $data);
        $modify->set('updated', (new DateTime())->format('Y-m-d H:i:s'));
        $modify->save();

        $written = true;

        return true;
    }

    public function destroy($id): bool
    {
        $modify = new Modify(\Container::$dbConnection, 'sessions');
        $modify->setId($id);
        $modify->delete();

        return true;
    }

    public function gc($maxlifetime): int
    {
        $select = new Select(\Container::$dbConnection, 'sessions');
        $select->columns(['id']);
        $select->where('updated', '<', (new DateTime('-' . $maxlifetime . ' seconds'))->format('Y-m-d H:i:s'));

        $result = $select->execute();

        while ($session = $result->fetch(IResultSet::FETCH_ASSOC)) {
            $modify = new Modify(\Container::$dbConnection, 'sessions');
            $modify->setId($session['id']);
            $modify->delete();
        }

        return true;
    }

    public function create_sid(): string
    {
        return hash('sha256', random_bytes(10) . microtime());
    }

    public function validateId($id): bool
    {
        return preg_match('/^[a-f0-9]{64}$/', $id);
    }

    public function updateTimestamp($id, $data): bool
    {
        if ($this->written) {
            return true;
        }

        $modify = new Modify(\Container::$dbConnection, 'sessions');

        $modify->setId($id);
        $modify->set('updated', (new DateTime())->format('Y-m-d H:i:s'));
        $modify->save();

        return true;
    }
}
