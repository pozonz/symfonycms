<?php

namespace SymfonyCMS\Engine\Cms\_Core\Version;

Interface VersionInterface
{
    /**
     * @return mixed
     */
    public function canBeRestored();

    /**
     * @param $versionUuid
     * @return mixed
     */
    public function getByVersionUuid($versionUuid);

    /**
     * @return mixed
     */
    public function savePreview();

    /**
     * @return mixed
     */
    public function saveDraft();

    /**
     * @return mixed
     */
    public function saveVersion();
}