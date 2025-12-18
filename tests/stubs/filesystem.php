<?php
/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OC\Files {
	class Filesystem {
		/**
		 * @param string $wrapperName
		 * @param callable $wrapper
		 * @param int $priority
		 */
		public static function addStorageWrapper($wrapperName, $wrapper, $priority = 50) {
		}
	}
}


namespace OC\Files\Storage\Wrapper{

	use OCP\Files\Cache\ICache;
	use OCP\Files\Cache\IPropagator;
	use OCP\Files\Cache\IScanner;
	use OCP\Files\Cache\IUpdater;
	use OCP\Files\Cache\IWatcher;
	use OCP\Files\Storage;
	use OCP\Files\Storage\IStorage;
	use OCP\Lock\ILockingProvider;

	class Wrapper implements IStorage {
		/**
		 * @var \OCP\Files\Storage\IStorage $storage
		 */
		protected $storage;

		/**
		 * @param array $parameters
		 */
		public function __construct($parameters) {
		}

		public function getWrapperStorage(): IStorage {
		}

		public function getId(): string {
		}

		public function mkdir($path): bool {
		}

		public function rmdir($path): bool {
		}

		public function opendir($path) {
		}

		public function is_dir($path): bool {
		}

		public function is_file($path): bool {
		}

		public function stat($path): array|false {
		}

		public function filetype($path): string|false {
		}

		public function filesize($path): int|float|false {
		}

		public function isCreatable($path): bool {
		}

		public function isReadable($path): bool {
		}

		public function isUpdatable($path): bool {
		}

		public function isDeletable($path): bool {
		}

		public function isSharable($path): bool {
		}

		public function getPermissions($path): int {
		}

		public function file_exists($path): bool {
		}

		public function filemtime($path): int|false {
		}

		public function file_get_contents($path): string|false {
		}

		public function file_put_contents($path, $data): int|float|false {
		}

		public function unlink($path): bool {
		}

		public function rename($source, $target): bool {
		}

		public function copy($source, $target): bool {
		}

		public function fopen($path, $mode) {
		}

		public function getMimeType($path): string|false {
		}

		public function hash($type, $path, $raw = false): string|false {
		}

		public function free_space($path): int|float|false {
		}

		public function touch($path, $mtime = null): bool {
		}

		public function getLocalFile($path): string|false {
		}

		public function hasUpdated($path, $time): bool {
		}

		public function getCache($path = '', $storage = null): ICache {
		}

		public function getScanner($path = '', $storage = null): IScanner {
		}

		public function getOwner($path): string|false {
		}

		public function getWatcher($path = '', $storage = null): IWatcher {
		}

		public function getPropagator($storage = null): IPropagator {
		}

		public function getUpdater($storage = null): IUpdater {
		}

		public function getStorageCache(): \OC\Files\Cache\Storage {
		}

		public function getETag($path): string|false {
		}

		public function test(): bool {
		}

		public function isLocal(): bool {
		}

		public function instanceOfStorage($class): bool {
		}

		/**
		 * @psalm-template T of IStorage
		 * @psalm-param class-string<T> $class
		 * @psalm-return T|null
		 */
		public function getInstanceOfStorage(string $class): ?IStorage {
		}

		/**
		 * Pass any methods custom to specific storage implementations to the wrapped storage
		 *
		 * @param string $method
		 * @param array $args
		 * @return mixed
		 */
		public function __call($method, $args) {
		}

		public function getDirectDownload(string $path): array|false {
		}

		public function getDirectDownloadById(string $fileId): array|false {
		}

		public function getAvailability(): array {
		}

		public function setAvailability($isAvailable): void {
		}

		public function verifyPath($path, $fileName): void {
		}

		public function copyFromStorage(IStorage $sourceStorage, $sourceInternalPath, $targetInternalPath): bool {
		}

		public function moveFromStorage(IStorage $sourceStorage, $sourceInternalPath, $targetInternalPath): bool {
		}

		public function getMetaData($path): ?array {
		}

		public function acquireLock($path, $type, ILockingProvider $provider): void {
		}

		public function releaseLock($path, $type, ILockingProvider $provider): void {
		}

		public function changeLock($path, $type, ILockingProvider $provider): void {
		}

		public function needsPartFile(): bool {
		}

		public function writeStream(string $path, $stream, ?int $size = null): int {
		}

		public function getDirectoryContent($directory): \Traversable {
		}

		public function isWrapperOf(IStorage $storage): bool {
		}

		public function setOwner(?string $user): void {
		}
	}

	class Jail extends Wrapper {
		public function getUnjailedPath(string $path): string {
		}
	}

	class Quota extends Wrapper {
		public function getQuota() {
		}
	}

	class PermissionsMask extends Wrapper {
		public function getQuota() {
		}
	}
}

namespace OC\Files\Cache {
	use OCP\Files\Cache\ICache;
	use OCP\Files\Cache\ICacheEntry;
	use OCP\Files\IMimeTypeLoader;
	use OCP\Files\Search\ISearchOperator;
	use OCP\Files\Search\ISearchQuery;

	class Cache implements ICache {
		/**
		 * @param \OCP\Files\Cache\ICache $cache
		 */
		public function __construct($cache) {
			$this->cache = $cache;
		}
		public function getNumericStorageId() {
		}
		public function getIncomplete() {
		}
		public function getPathById($id) {
		}
		public function getAll() {
		}
		public function get($file) {
		}
		public function getFolderContents($folder) {
		}
		public function getFolderContentsById($fileId) {
		}
		public function put($file, array $data) {
		}
		public function insert($file, array $data) {
		}
		public function update($id, array $data) {
		}
		public function getId($file) {
		}
		public function getParentId($file) {
		}
		public function inCache($file) {
		}
		public function remove($file) {
		}
		public function move($source, $target) {
		}
		public function moveFromCache(ICache $sourceCache, $sourcePath, $targetPath) {
		}
		public function clear() {
		}
		public function getStatus($file) {
		}
		public function search($pattern) {
		}
		public function searchByMime($mimetype) {
		}
		public function searchQuery(ISearchQuery $query) {
		}
		public function correctFolderSize($path, $data = null, $isBackgroundScan = false) {
		}
		public function copyFromCache(ICache $sourceCache, ICacheEntry $sourceEntry, string $targetPath): int {
		}
		public function normalize($path) {
		}
		public function getQueryFilterForStorage(): ISearchOperator {
		}
		public function getCacheEntryFromSearchResult(ICacheEntry $rawEntry): ?ICacheEntry {
		}
		public static function cacheEntryFromData($data, IMimeTypeLoader $mimetypeLoader) {
		}
	}
}

namespace OC\Files\Cache\Wrapper {
	use OC\Files\Cache\Cache;

	class CacheWrapper extends Cache {
	}
	class CachePermissionsMask extends CacheWrapper {
		/**
		 * @param \OCP\Files\Cache\ICache $cache
		 * @param int $mask
		 */
		public function __construct($cache, $mask) {
		}

		protected function formatCacheEntry($entry) {
		}
	}
}
