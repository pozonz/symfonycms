<?php

namespace ExWife\Engine\Cms\_Core\Version;

use Ramsey\Uuid\Uuid;

trait VersionTrait
{
    /**
     * @return bool
     */
    public function canBeRestored()
    {
        return true;
    }

    /**
     * @param $versionUuid
     * @return mixed
     */
    public function getByVersionUuid($versionUuid)
    {
        $orm = static::data($this->_connection, [
            'whereSql' => 'm._versionUuid = ?',
            'params' => [$versionUuid],
            'limit' => 1,
            'oneOrNull' => 1,
            'includePreviousVersion' => 1,
        ]);
        if ($orm) {
            $orm->_originalOrm = $this;
        }
        return $orm;
    }

    /**
     * @return mixed|null
     */
    public function savePreview()
    {
        $this->id = null;
        $this->_uniqid = Uuid::uuid4();
        $this->_added = date('Y-m-d H:i:s');
        $this->_versionOrmId = 0;
        $this->_versionUuid = Uuid::uuid4();
        $this->save();

        return $this;
    }

    /**
     * @param string $draftName
     * @return mixed|null
     */
    public function saveDraft($draftName = '')
    {
        if (!$this->id) {
            $this->_status = 0;
            $this->save();
        }

        $oldOrm = clone $this;

        $this->id = null;
        $this->_uniqid = Uuid::uuid4();
        $this->_added = date('Y-m-d H:i:s');
        $this->_versionOrmId = $oldOrm->id;
        $this->_versionUuid = Uuid::uuid4();
        $this->_isDraft = 1;
        $this->_draftName = $draftName;
        $this->save();

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function saveVersion()
    {
        $newOrm = static::data($this->_connection, [
            'whereSql' => 'm.id = ?',
            'params' => [$this->id],
            'limit' => 1,
            'oneOrNull' => 1,
        ]);

        if ($newOrm) {
            $newOrm->_versionOrmId = $this->id;
            $newOrm->_versionUuid = Uuid::uuid4();
            $newOrm->id = null;
            $newOrm->_uniqid = Uuid::uuid4();
            $newOrm->_added = $this->_modified;
            $newOrm->_isDraft = 0;
            $newOrm->save([
                'doNotUpdateModified' => 1,
            ]);
        }

        return $newOrm;
    }
}