<?php
declare(strict_types = 1);

namespace NaiveSerializer;

use Assert\Assertion;
use LogicException;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use ReflectionClass;
use ReflectionProperty;

final class JsonSerializer
{
    private ContextFactory $contextFactory;
    private DocBlockFactory $docblockFactory;
    private TypeResolver $typeResolver;

    public function __construct()
    {
        $this->contextFactory = new ContextFactory();
        $this->docblockFactory = DocBlockFactory::createInstance();
        $this->typeResolver = new TypeResolver();
    }

    /**
     * @return array|bool|float|int|mixed|string|null
     */
    public function deserialize(string $type, string $jsonEncodedData)
    {
        $resolvedType = $this->typeResolver->resolve($type);

        return self::restoreDataStructure($resolvedType, $this->jsonDecode($jsonEncodedData));
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    private function restoreDataStructure(Type $type, $data)
    {
        if ($data === null) {
            // TODO verify that null is allowed
            return null;
        }
        if ($type instanceof String_) {
            return (string)$data;
        }
        if ($type instanceof Integer) {
            return (integer)$data;
        }
        if ($type instanceof Boolean) {
            return (boolean)$data;
        }
        if ($type instanceof Float_) {
            return (float)$data;
        }

        if ($type instanceof Object_) {
            Assertion::isArray($data);
            $class = (string)$type;
            Assertion::classExists($class);
            /** @var class-string $class */

            $reflection = new ReflectionClass($class);
            if (!$reflection->isUserDefined()) {
                throw new LogicException(sprintf('Class "%s" is not user-defined', $type));
            }

            $object = $reflection->newInstanceWithoutConstructor();
            foreach ($reflection->getProperties() as $property) {
                if (!array_key_exists($property->getName(), $data)) {
                    continue;
                }

                $propertyType = $this->resolvePropertyType($property, $reflection);
                $property->setAccessible(true);
                $property->setValue($object, self::restoreDataStructure($propertyType, $data[$property->getName()]));
            }
            return $object;
        }

        if ($type instanceof Array_) {
            $processed = [];
            foreach ($data as $key => $elementData) {
                $processed[$key] = self::restoreDataStructure($type->getValueType(), $elementData);
            }

            return $processed;
        }

        if ($type instanceof Compound) {
            $innerTypes = iterator_to_array($type);
            if (count($innerTypes) === 2) {
                if ($innerTypes[0] instanceof Null_) {
                    return $this->restoreDataStructure($innerTypes[1], $data);
                } elseif ($innerTypes[1] instanceof Null_) {
                    return $this->restoreDataStructure($innerTypes[0], $data);
                }
            }
        }
        throw new LogicException('Unsupported type: ' . get_class($type));
    }

    /**
     * @param mixed $rawData
     */
    public function serialize($rawData): string
    {
        $result = json_encode($this->extractSerializableDataFrom($rawData), JSON_PRETTY_PRINT);
        Assertion::string($result, 'JSON decoding failed');

        return $result;
    }

    /**
     * @param mixed $something
     * @return array|bool|float|int|string|null
     */
    private function extractSerializableDataFrom($something)
    {
        if (is_object($something)) {
            $data = [];

            $reflection = new ReflectionClass(get_class($something));
            if (!$reflection->isUserDefined()) {
                throw new LogicException(sprintf('Class "%s" is not user-defined', $reflection->getName()));
            }

            foreach ($reflection->getProperties() as $property) {
                if (strpos($property->getDocComment() ?: '', '@ignore') !== false) {
                    continue;
                }
                $property->setAccessible(true);
                $data[$property->getName()] = $this->extractSerializableDataFrom($property->getValue($something));
            }

            return $data;
        }

        if (is_array($something)) {
            $data = [];
            foreach ($something as $key => $element) {
                $data[$key] = $this->extractSerializableDataFrom($element);
            }

            return $data;
        }

        if (is_scalar($something) || $something === null) {
            return $something;
        }

        throw new LogicException(sprintf(
            'Unsupported type: "%s" (%s). You can only serialize objects, arrays and scalar values.',
            gettype($something),
            var_export($something, true)
        ));
    }

    private function resolvePropertyType(ReflectionProperty $property, ReflectionClass $class) : Type
    {
        $fileName = $class->getFileName() ?: '';
        Assertion::file($fileName, sprintf(
            'Class "%s" has no source file, maybe it is a PHP built-in class?',
            $class->getName()
        ));
        $fileContents = file_get_contents($fileName);
        Assertion::string($fileContents, sprintf('Could not load contents of file "%s"', $fileName));
        $context = $this->contextFactory->createForNamespace(
            $class->getNamespaceName(),
            $fileContents
        );

        $docComment = $property->getDocComment() ?: '';
        Assertion::notEmpty($docComment, sprintf('You need to add a docblock to property "%s"', $property->getName()));

        $docblock = $this->docblockFactory->create($docComment, $context);
        $varTags = $docblock->getTagsByName('var');
        Assertion::count(
            $varTags,
            1,
            sprintf('You need to add an @var annotation to property "%s"', $property->getName())
        );
        $varTag = $varTags[0];
        Assertion::isInstanceOf($varTag, Var_::class);
        /** @var Var_ $varTag */
        $propertyType = $varTag->getType();
        Assertion::isInstanceOf($propertyType, Type::class, 'Could not derive a type from this @var annotation: ' . (string)$varTag);

        return $propertyType;
    }

    private function jsonDecode(string $jsonEncodedData) : array
    {
        $decoded = json_decode($jsonEncodedData, true);
        if ($decoded === null && json_last_error()) {
            throw new LogicException('You provided invalid JSON: ' . json_last_error_msg());
        }

        return $decoded;
    }
}
