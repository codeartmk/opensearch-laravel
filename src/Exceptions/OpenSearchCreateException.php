<?php

namespace Codeart\OpensearchLaravel\Exceptions;

/**
 * Thrown when a bulk indexing request reports errors.
 *
 * The message is kept short and log-safe: it holds only the index name and counts. The bulk response echoes
 * per-document errors whose reasons can quote document values, so the details are only available through the getters.
 */
class OpenSearchCreateException extends \Exception implements OpenSearchException
{
    private readonly array $failedItems;

    /**
     * @param string $index The index the bulk request was sent to
     * @param array<string, mixed> $response The raw bulk response of the failing request
     * @param int $indexedCount Documents indexed successfully before the exception, including the successes in the failing request
     */
    public function __construct(
        string $index,
        private readonly array $response,
        private readonly int $indexedCount
    )
    {
        $this->failedItems = self::extractFailedItems($response);

        $itemCount = count($response['items'] ?? []);
        $failedCount = count($this->failedItems);

        $message = "Bulk indexing into '$index' failed: $failedCount of $itemCount "
            . self::pluralize($itemCount, 'document') . ' in the current chunk '
            . ($failedCount === 1 ? 'was' : 'were') . ' rejected. '
            . "$indexedCount " . self::pluralize($indexedCount, 'document') . ' '
            . ($indexedCount === 1 ? 'was' : 'were') . ' indexed before the failure.';

        parent::__construct($message, 500);
    }

    /**
     * The rejected documents of the failing request, each as ['_id' => ..., 'status' => ..., 'error' => [...]].
     * The error reasons can quote document values, so treat them like the documents themselves.
     *
     * @return list<array{_id: mixed, status: mixed, error: array<string, mixed>}>
     */
    public function getFailedItems(): array
    {
        return $this->failedItems;
    }

    /**
     * Documents indexed successfully before the exception was thrown: those in earlier chunks plus the successes
     * inside the failing chunk. They stay in the index.
     */
    public function getIndexedCount(): int
    {
        return $this->indexedCount;
    }

    /**
     * The raw bulk response of the failing request.
     *
     * @return array<string, mixed>
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    private static function extractFailedItems(array $response): array
    {
        $failedItems = [];

        foreach ($response['items'] ?? [] as $item) {
            foreach ((array)$item as $result) {
                if (!is_array($result) || !isset($result['error'])) {
                    continue;
                }

                $failedItems[] = [
                    '_id' => $result['_id'] ?? null,
                    'status' => $result['status'] ?? null,
                    'error' => (array)$result['error'],
                ];
            }
        }

        return $failedItems;
    }

    private static function pluralize(int $count, string $word): string
    {
        return $count === 1 ? $word : "{$word}s";
    }
}
