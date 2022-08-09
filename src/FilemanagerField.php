<?php

namespace Grayloon\Filemanager;

use Illuminate\Validation\Rule;
use Grayloon\Filemanager\Http\Services\FileManagerService;
use Grayloon\Filemanager\Traits\CoverHelpers;
use Laravel\Nova\Contracts\Cover;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class FilemanagerField extends Field implements Cover
{
    use CoverHelpers;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'filemanager-field';

    /**
     * The validation rules for upload files.
     *
     * @var array
     */
    public array $uploadRules = [];

    /**
     * @var bool
     */
    protected  bool $createFolderButton;

    /**
     * @var bool
     */
    protected  bool $uploadButton;

    /**
     * @var bool
     */
    protected bool $dragAndDropUpload;

    /**
     * @var bool
     */
    protected bool $renameFolderButton;

    /**
     * @var bool
     */
    protected bool $deleteFolderButton;

    /**
     * @var bool
     */
    protected bool $renameFileButton;

    /**
     * @var bool
     */
    protected bool $deleteFileButton;

    /**
     * @var bool
     */
    protected bool $downloadFileButton;

    /**
     * The callback used to determine if the field is readonly.
     *
     * @var Closure
     */
    public $readonlyCallback;

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  mixed|null  $resolveCallback
     * @return void
     */
    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->setButtons();

        $this->withMeta(['visibility' => 'public']);
        $this->rounded();
    }

    /**
     * Set display in details and list as image or icon.
     *
     * @return $this
     */
    public function displayAsImage(): self
    {
        return $this->withMeta(['display' => 'image']);
    }

    /**
     * Set current folder for the field.
     *
     * @param   string  $folderName
     *
     * @return  $this
     */
    public function folder($folderName): self
    {
        $folder = is_callable($folderName) ? call_user_func($folderName) : $folderName;

        return $this->withMeta(['folder' => $folder, 'home' => $folder]);
    }

    /**
     * Set current folder for the field.
     *
     * @param   string | function  $rules
     *
     * @return  $this
     */
    public function validateUpload($rules): self
    {
        $this->uploadRules = ($rules instanceof Rule || is_string($rules)) ? func_get_args() : $rules;

        return $this;
    }

    /**
     * Set filter for the field.
     *
     * @param   string  $filter
     *
     * @return  $this
     */
    public function filterBy(string $filter): self
    {
        $defaultFilters = config('filemanager.filters', []);

        if (count($defaultFilters) > 0) {
            $filters = array_change_key_case($defaultFilters);

            if (isset($filters[$filter])) {
                $filteredExtensions = $filters[$filter];

                return $this->withMeta(['filterBy' => $filter]);
            }
        }

        return $this;
    }

    /**
     * Set display in details and list as image or icon.
     *
     * @return $this
     */
    public function privateFiles(): self
    {
        return $this->withMeta(['visibility' => 'private']);
    }

    /**
     * Hide Create button Folder.
     *
     * @return $this
     */
    public function hideCreateFolderButton(): self
    {
        $this->createFolderButton = false;

        return $this;
    }

    /**
     * Hide Upload button.
     *
     * @return $this
     */
    public function hideUploadButton(): self
    {
        $this->uploadButton = false;

        return $this;
    }

    /**
     * Hide Rename folder button.
     *
     * @return $this
     */
    public function hideRenameFolderButton(): self
    {
        $this->renameFolderButton = false;

        return $this;
    }

    /**
     * Hide Delete folder button.
     *
     * @return $this
     */
    public function hideDeleteFolderButton(): self
    {
        $this->deleteFolderButton = false;

        return $this;
    }

    /**
     * Hide Rename file button.
     *
     * @return $this
     */
    public function hideRenameFileButton(): self
    {
        $this->renameFileButton = false;

        return $this;
    }

    /**
     * Hide Rename file button.
     *
     * @return $this
     */
    public function hideDeleteFileButton(): self
    {
        $this->deleteFileButton = false;

        return $this;
    }

    /**
     * Hide Rename file button.
     *
     * @return $this
     */
    public function hideDownloadFileButton(): self
    {
        $this->downloadFileButton = false;

        return $this;
    }

    /**
     * No drag and drop file upload.
     *
     * @return $this
     */
    public function noDragAndDropUpload(): self
    {
        $this->dragAndDropUpload = false;

        return $this;
    }

    /**
     * Set the callback used to determine if the field is readonly.
     *
     * @param  Closure|bool  $callback
     * @return $this
     */
    public function readonly($callback = true): self
    {
        $this->readonlyCallback = $callback;

        return $this;
    }

    /**
     * Determine if the field is readonly.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return bool
     */
    public function isReadonly(NovaRequest $request): bool
    {
        return with($this->readonlyCallback, function ($callback) use ($request) {
            if ($callback === true || (is_callable($callback) && call_user_func($callback, $request))) {
                $this->setReadonlyAttribute();

                return true;
            }

            return false;
        });
    }

    /**
     * Set the field to a readonly field.
     *
     * @return $this
     */
    protected function setReadonlyAttribute(): self
    {
        $this->withMeta(['extraAttributes' => ['readonly' => true]]);

        return $this;
    }

	/**
	 * Resolve the thumbnail URL for the field.
	 *
	 * @return array|string|null
	 */
    public function resolveInfo(): array|string|null
    {
        if ($this->value) {
            $service = new FileManagerService();

            $data = $service->getFileInfoAsArray($this->value);

            if (empty($data)) {
                return [];
            }

            return $this->fixNameLabel($data);
        }

        return [];
    }

    /**
     * Resolve the thumbnail URL for the field.
     *
     * @return string|null
     */
    public function resolveThumbnailUrl(): ?string
    {
        if ($this->value) {
            $service = new FileManagerService();

            $data = $service->getFileInfoAsArray($this->value);

            if ((isset($data['type']) && $data['type'] !== 'image') || empty($data)) {
                return null;
            }

            return $data['url'];
        }

		return null;
    }

    /**
     * Get additional meta information to merge with the element payload.
     *
     * @return array
     */
    public function meta(): array
    {
        return array_merge(
            $this->resolveInfo(),
            $this->buttons(),
            $this->getUploadRules(),
            $this->getCoverType(),
            $this->meta
        );
    }

    /**
     * Set default button options.
     */
    private function setButtons()
    {
        $this->createFolderButton = config('filemanager.buttons.create_folder', true);
        $this->uploadButton = config('filemanager.buttons.upload_button', true);
        $this->dragAndDropUpload = config('filemanager.buttons.upload_drag', true);
        $this->renameFolderButton = config('filemanager.buttons.rename_folder', true);
        $this->deleteFolderButton = config('filemanager.buttons.delete_folder', true);
        $this->renameFileButton = config('filemanager.buttons.rename_file', true);
        $this->deleteFileButton = config('filemanager.buttons.delete_file', true);
        $this->downloadFileButton = config('filemanager.buttons.download_file', true);
    }

    /**
     * Return correct buttons.
     *
     * @return array
     */
    private function buttons(): array
    {
        $buttons = [
            'create_folder' => $this->createFolderButton,
            'upload_button' => $this->uploadButton,
            'upload_drag' => $this->dragAndDropUpload,
            'rename_folder' => $this->renameFolderButton,
            'delete_folder' => $this->deleteFolderButton,
            'rename_file' => $this->renameFileButton,
            'delete_file' => $this->deleteFileButton,
            'download_file' => $this->downloadFileButton,
        ];

        return ['buttons' => $buttons];
    }

    /**
     * Return upload rules.
     *
     * @return  array
     */
    private function getUploadRules(): array
    {
        return ['upload_rules' => $this->uploadRules];
    }

    /**
     * Return cover type.
     *
     * @return  array
     */
    private function getCoverType(): array
    {
        return ['rounded' => $this->isRounded()];
    }

    /**
     * FIx name label.
     *
     * @param array $data
     *
     * @return array
     */
    private function fixNameLabel(array $data): array
    {
        $data['filename'] = $data['name'];
        unset($data['name']);

        return $data;
    }
}
