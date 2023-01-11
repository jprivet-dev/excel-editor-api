<?php

namespace App\Serializer\Normalizer;

use App\Entity\Data;
use App\Util\ArrayUtil;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DataNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(private ObjectNormalizer $normalizer)
    {
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        if (!$object instanceof Data) {
            throw new InvalidArgumentException(sprintf('The object must implement "%s".', Data::class));
        }

        // @see https://symfony.com/doc/current/components/serializer.html#using-callbacks-to-serialize-properties-with-object-instances
        $callback = fn(
            $innerObject,
            $outerObject,
            string $attributeName,
            string $format = null,
            array $context = []
        ) => $innerObject instanceof \DateTime ? $innerObject->format('Y') : null;

        $context[AbstractNormalizer::CALLBACKS] = [
            'anneeDebut' => $callback,
            'anneeSeparation' => $callback,
        ];

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Data;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): Data
    {
        $data = ArrayUtil::trim($data);
        $data = ArrayUtil::emptyStringsAsNull($data);

        return $this->normalizer->denormalize($data, Data::class, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return Data::class === $type;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
