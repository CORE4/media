<?php
namespace TYPO3\Media\Model\Adjustment;

/*
 * This file is part of the TYPO3.Media package.
 */

use Imagine\Image\ImageInterface as ImagineImageInterface;
use Imagine\Image\Point;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Resource\Resource;
use TYPO3\Media\Domain\Model\Adjustment\AbstractImageAdjustment;
use TYPO3\Media\Domain\Model\Image;
use TYPO3\Media\Domain\Model\ImageInterface;
use TYPO3\Media\Domain\Service\ImageAdjustmentService;
use TYPO3\Media\Exception\InvalidImageAdjustmentConfigurationException;

/**
 * An adjustment for applying a watermark to an image
 *
 * @Flow\Entity
 */
class WatermarkAdjustment extends AbstractImageAdjustment
{

    const VERTICAL_POSITION_TOP = 'top';
    const VERTICAL_POSITION_MIDDLE = 'middle';
    const VERTICAL_POSITION_BOTTOM = 'bottom';

    const HORIZONTAL_POSITION_LEFT = 'left';
    const HORIZONTAL_POSITION_CENTER = 'center';
    const HORIZONTAL_POSITION_RIGHT = 'right';


    /**
     * @var \Imagine\Image\ImagineInterface
     * @Flow\Inject
     */
    protected $imagineService;

    /**
     * @Flow\Inject
     * @var ImageAdjustmentService
     */
    protected $imageAdjustmentService;


    /**
     * @var integer
     */
    protected $position = 30;

    /**
     * @var array
     */
    protected $configuration = [
        'watermark' => null,
        'verticalPosition' => self::VERTICAL_POSITION_TOP,
        'verticalOffset' => 0,
        'horizontalPosition' => self::HORIZONTAL_POSITION_LEFT,
        'horizontalOffset' => 0
    ];


    /**
     * @param ImagineImageInterface|ImageInterface|Resource|resource|string $watermark
     * @return void
     * @throws InvalidImageAdjustmentConfigurationException
     */
    public function setWatermarkConfiguration($watermark)
    {
        if ($watermark instanceof ImagineImageInterface) {
            $this->setWatermark($watermark);
        } elseif ($watermark instanceof ImageInterface) {
            $this->setWatermark($this->imagineService->read($watermark->getResource()->getStream()));
        } elseif ($watermark instanceof Resource) {
            $this->setWatermark($this->imagineService->read($watermark->getStream()));
        } elseif (is_resource($watermark)) {
            $this->setWatermark($this->imagineService->read($watermark));
        } elseif (is_string($watermark)) {
            $this->setWatermark($this->imagineService->open($watermark));
        } else {
            throw new InvalidImageAdjustmentConfigurationException(
                'Invalid watermark given.',
                1462180720
            );
        }
    }

    /**
     * @return ImagineImageInterface|null
     */
    public function getWatermark()
    {
        return $this->getConfigurationValue('watermark');
    }

    /**
     * @param ImagineImageInterface $watermark
     * @return void
     */
    public function setWatermark(ImagineImageInterface $watermark)
    {
        $this->setConfigurationValue('watermark', $watermark);
    }

    /**
     * @return string
     */
    public function getVerticalPosition()
    {
        return $this->getConfigurationValue('verticalPosition');
    }

    /**
     * @param string $verticalPosition
     * @throws InvalidImageAdjustmentConfigurationException
     * @return void
     */
    public function setVerticalPosition($verticalPosition)
    {
        if ($verticalPosition === self::VERTICAL_POSITION_TOP
            || $verticalPosition === self::VERTICAL_POSITION_MIDDLE
            || $verticalPosition === self::VERTICAL_POSITION_BOTTOM
        ) {
            $this->setConfigurationValue('verticalPosition', $verticalPosition);
        } else {
            $this->throwInvalidVerticalPositionException($verticalPosition);
        }
    }

    /**
     * @return string
     */
    public function getHorizontalPosition()
    {
        return $this->getConfigurationValue('horizontalPosition');
    }

    /**
     * @param string $horizontalPosition
     * @throws InvalidImageAdjustmentConfigurationException
     * @return void
     */
    public function setHorizontalPosition($horizontalPosition)
    {
        if ($horizontalPosition === self::VERTICAL_POSITION_TOP
            || $horizontalPosition === self::VERTICAL_POSITION_MIDDLE
            || $horizontalPosition === self::VERTICAL_POSITION_BOTTOM
        ) {
            $this->setConfigurationValue('horizontalPosition', $horizontalPosition);
        } else {
            $this->throwInvalidHorizontalPositionException($horizontalPosition);
        }
    }

    /**
     * @return integer
     */
    public function getHorizontalOffset()
    {
        return $this->getConfigurationValue('horizontalOffset');
    }

    /**
     * @param integer $horizontalOffset
     * @return void
     */
    public function setHorizontalOffset($horizontalOffset)
    {
        $this->setConfigurationValue('horizontalOffset', $horizontalOffset);
    }

    /**
     * @return integer
     */
    public function getVerticalOffset()
    {
        return $this->getConfigurationValue('verticalOffset');
    }

    /**
     * @param integer $verticalOffset
     * @return void
     */
    public function setVerticalOffset($verticalOffset)
    {
        $this->setConfigurationValue('verticalOffset', $verticalOffset);
    }


    /**
     * Check if this adjustment can or should be applied to the image.
     *
     * @param ImagineImageInterface $image
     * @return boolean
     */
    public function canBeApplied(ImagineImageInterface $image)
    {
        return $this->getWatermark() instanceof ImagineImageInterface;
    }

    /**
     * Applies this adjustment to the given Imagine Image object
     *
     * @param ImagineImageInterface $image
     * @return ImagineImageInterface
     * @internal Should never be used outside of the media package. Rely on the ImageService to apply your adjustments.
     */
    public function applyToImage(ImagineImageInterface $image)
    {
        return $this->applyWatermark($image, $this->getWatermark());
    }


    /**
     * Executes the actual watermarking operation on the Imagine image.
     *
     * @param ImagineImageInterface $image
     * @param ImagineImageInterface $watermark
     * @return \Imagine\Image\ManipulatorInterface
     */
    protected function applyWatermark(ImagineImageInterface $image, ImagineImageInterface $watermark)
    {
        $watermarkedImage = $image->copy();
        $watermarkedImage->usePalette($image->palette());
        $watermarkedImage->strip();

        $target = $this->imageAdjustmentService->calculateWatermarkPosition(
            $image,
            $watermark,
            $this->getHorizontalPosition(),
            $this->getHorizontalOffset(),
            $this->getVerticalPosition(),
            $this->getVerticalOffset()
        );

        $image->paste($watermark, $target);

        return $watermarkedImage;
    }

    /**
     * @param $horizontalPosition
     * @throws InvalidImageAdjustmentConfigurationException
     * @return void
     */
    protected function throwInvalidHorizontalPositionException($horizontalPosition) {
        throw new InvalidImageAdjustmentConfigurationException(
            'Invalid horizontal position "' . $horizontalPosition . '", must be one of the defined class constants.',
            1462180421
        );
    }

    /**
     * @param $verticalPosition
     * @throws InvalidImageAdjustmentConfigurationException
     * @return void
     */
    protected function throwInvalidVerticalPositionException($verticalPosition) {
        throw new InvalidImageAdjustmentConfigurationException(
            'Invalid vertical position "' . $verticalPosition . '", must be one of the defined class constants.',
            1462180338
        );
    }

}
