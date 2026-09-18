<?php

namespace Codeart\OpensearchLaravel\Search\SearchQueries;

use Codeart\OpensearchLaravel\Exceptions\InvalidSearchParametersException;
use Codeart\OpensearchLaravel\Interfaces\OpenSearchQuery;

/**
 * A `bool` query: combines Must, Should, MustNot and Filter clauses, each at most once.
 *
 * @see https://opensearch.org/docs/latest/query-dsl/compound/bool/
 */
class BoolQuery implements OpenSearchQuery
{
    private const CLAUSES = [Must::class, Should::class, MustNot::class, Filter::class];

    private const OPTIONS = ['minimum_should_match', 'boost'];

    /**
     * @param array<int|string, Must|Should|MustNot|Filter|int|float|string|null> $parameters Must, Should, MustNot and
     *        Filter clauses, plus the optional minimum_should_match and boost keys, see make()
     * @throws InvalidSearchParametersException When an item is not a clause, a string key is neither
     *                                          minimum_should_match nor boost, an option value has the wrong type,
     *                                          or a clause kind appears twice
     */
    public function __construct(
        private readonly array $parameters
    ) {
        $clauses = [];

        foreach ($parameters as $key => $parameter) {
            // Checked before the clause match: a clause under an option key would otherwise be sent twice.
            if (is_string($key) && in_array($key, self::OPTIONS, true)) {
                self::validateOption($key, $parameter);

                continue;
            }

            $clause = self::clauseOf($parameter);

            if (!is_null($clause)) {
                if (isset($clauses[$clause])) {
                    throw new InvalidSearchParametersException(sprintf(
                        'BoolQuery accepts only one %s clause.',
                        class_basename($clause)
                    ));
                }

                $clauses[$clause] = true;

                continue;
            }

            if (is_string($key)) {
                throw new InvalidSearchParametersException(
                    "BoolQuery accepts only the minimum_should_match and boost options, \"$key\" given."
                );
            }

            throw new InvalidSearchParametersException(sprintf(
                'BoolQuery accepts only Must, Should, MustNot and Filter clauses, %s given.',
                get_debug_type($parameter)
            ));
        }
    }

    /**
     * The clause objects and the two options share one array: clauses as list items (subclasses count,
     * matched with instanceof), options under their string keys.
     *
     * ```php
     * BoolQuery::make([
     *     Must::make(Term::make('status', 'active')),
     *     Should::make([MatchOne::make('title', 'wind'), MatchOne::make('body', 'wind')]),
     *     'minimum_should_match' => 1,
     *     'boost' => 1.5,
     * ]);
     * ```
     *
     * `minimum_should_match` takes an int or a string (`1`, `-1`, `"75%"`, `"3<90%"`); OpenSearch rejects a float.
     * `boost` takes an int, a float or a numeric string (`1.5`, `"2"`). A `null` under either key means "not set"
     * and is left out, so a conditional value can be passed directly. Anything else under an option key, a clause
     * object included, throws.
     *
     * `minimum_should_match` is only sent when a Should clause is present too: without one, OpenSearch would
     * match nothing, so it is dropped. `boost` is always sent when set. With no clauses and no options the
     * query is sent as `{"bool": {}}`.
     *
     * @param array<int|string, Must|Should|MustNot|Filter|int|float|string|null> $parameters
     * @return self
     * @throws InvalidSearchParametersException When an item is not a clause, a string key is neither
     *                                          minimum_should_match nor boost, an option value has the wrong type,
     *                                          or a clause kind appears twice
     */
    public static function make(array $parameters): self
    {
        return new self($parameters);
    }

    /**
     * Checks the type of an option value; null is allowed and means the option is not set.
     *
     * @throws InvalidSearchParametersException When minimum_should_match is not an int or string, or boost is not
     *                                          an int, float or numeric string
     */
    private static function validateOption(string $key, mixed $value): void
    {
        if (is_null($value)) {
            return;
        }

        $isValid = match ($key) {
            'minimum_should_match' => is_int($value) || is_string($value),
            'boost' => is_int($value) || is_float($value) || (is_string($value) && is_numeric($value)),
        };

        if ($isValid) {
            return;
        }

        throw new InvalidSearchParametersException(sprintf(
            'BoolQuery option "%s" must be %s, %s given.',
            $key,
            $key === 'boost' ? 'an int, a float or a numeric string' : 'an int or a string',
            get_debug_type($value)
        ));
    }

    /**
     * Returns the clause class the parameter is an instance of, or null when it isn't a clause.
     */
    private static function clauseOf(mixed $parameter): ?string
    {
        foreach (self::CLAUSES as $clause) {
            if ($parameter instanceof $clause) {
                return $clause;
            }
        }

        return null;
    }

    /**
     * @return array{bool: array{must?: mixed, should?: mixed, must_not?: mixed, filter?: mixed, minimum_should_match?: int|string, boost?: int|float|string}|\stdClass}
     */
    public function toOpenSearchQuery(): array
    {
        $resulting = [
            'bool' => []
        ];

        foreach ($this->parameters as $parameter) {
            if($parameter instanceof Must) {
                $resulting['bool']['must'] = $parameter->toOpenSearchQuery();
            }

            if($parameter instanceof Should) {
                $resulting['bool']['should'] = $parameter->toOpenSearchQuery();
            }

            if($parameter instanceof MustNot) {
                $resulting['bool']['must_not'] = $parameter->toOpenSearchQuery();
            }

            if($parameter instanceof Filter) {
                $resulting['bool']['filter'] = $parameter->toOpenSearchQuery();
            }
        }

        // Without a should clause, minimum_should_match would make the query match nothing, so it is left out.
        if(
            isset($this->parameters['minimum_should_match'])
            && isset($resulting['bool']['should'])
        ) {
            $resulting['bool']['minimum_should_match'] = $this->parameters['minimum_should_match'];
        }

        if(
            isset($this->parameters['boost'])
        ) {
            $resulting['bool']['boost'] = $this->parameters['boost'];
        }

        // An empty bool has to be sent as a JSON object; an empty array serialises to [], which OpenSearch rejects.
        if (!count($resulting['bool'])) {
            $resulting['bool'] = new \stdClass();
        }

        return $resulting;
    }
}
