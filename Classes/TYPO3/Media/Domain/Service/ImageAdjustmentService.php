<?php
namespace TYPO3\Media\Domain\Service;

/*
 * This file is part of the TYPO3.Media package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Imagine\Image\Point;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Media\Domain\Model\Adjustment\ImageAdjustmentInterface;
use TYPO3\Media\Exception\ImageFileException;
use TYPO3\Media\Exception\ImageServiceException;
use TYPO3\Media\Exception\InvalidImageAdjustmentConfigurationException;
use TYPO3\Media\Model\Adjustment\WatermarkAdjustment;
use Imagine\Image\ImageInterface as ImagineImageInterface;

/**
 * A service to support image adjustments
 *
 * @Flow\Scope("singleton")
 */
class ImageAdjustmentService
{

    /**
     * Calculates the watermark position for the given parameters
     *
     * @param ImagineImageInterface $image The original image to be watermarked
     * @param ImagineImageInterface $watermark The watermark to be applied
     * @param string $horizontalPosition The horizontal position to be used
     * @param integer $horizontalOffset The horizontal offset to be used
     * @param string $verticalPosition The vertical position to be used
     * @param integer $verticalOffset The vertical offset to be used
     * @return Point
     * @throws InvalidImageAdjustmentConfigurationException
     */
    public function calculateWatermarkPosition(ImagineImageInterface $image, ImagineImageInterface $watermark, $horizontalPosition, $horizontalOffset, $verticalPosition, $verticalOffset)
    {
        $imageSize = $image->getSize();
        $watermarkSize = $watermark->getSize();

        $x = 0;
        switch ($horizontalPosition) {
            case WatermarkAdjustment::HORIZONTAL_POSITION_LEFT:
                break;
            case WatermarkAdjustment::HORIZONTAL_POSITION_RIGHT:
                $x = $imageSize->getWidth() - $watermarkSize->getWidth();
                break;
            case WatermarkAdjustment::HORIZONTAL_POSITION_CENTER:
                $x = (integer)round(($imageSize->getWidth() - $watermarkSize->getWidth()) / 2);
                break;
            default:
                throw new InvalidImageAdjustmentConfigurationException(
                    'Invalid horizontal position "' . $horizontalPosition . '", must be one of the defined class constants.',
                    1462180421
                );
        }
        $x += $horizontalOffset;

        $y = 0;
        switch ($horizontalPosition) {
            case WatermarkAdjustment::VERTICAL_POSITION_TOP:
                break;
            case WatermarkAdjustment::VERTICAL_POSITION_BOTTOM:
                $y = $imageSize->getHeight() - $watermarkSize->getHeight();
                break;
            case WatermarkAdjustment::VERTICAL_POSITION_MIDDLE:
                $y = (integer)round(($imageSize->getHeight() - $watermarkSize->getHeight()) / 2);
                break;
            default:
                throw new InvalidImageAdjustmentConfigurationException(
                    'Invalid vertical position "' . $verticalPosition . '", must be one of the defined class constants.',
                    1462180338
                );
        }
        $y += $verticalOffset;

        return new Point($x, $y);
    }

}
