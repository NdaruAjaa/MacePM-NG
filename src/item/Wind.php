<?php

/*MIT License

Copyright (c) 2025 Jasson44

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.*/

declare(strict_types=1);

namespace XeonCh\Mace\item;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\ProjectileItem;
use pocketmine\player\Player;
use XeonCh\Mace\entity\WindCharge;

class Wind extends ProjectileItem
{

    public function getMaxStackSize(): int
    {
        return 64;
    }

    protected function createEntity(Location $location, Player $thrower): Throwable
    {
        return new WindCharge($location, $thrower);
    }

    public function getThrowForce(): float
    {
        return 1.5;
    }

    public function getCooldownTicks(): int
    {
        return 10;
    }

    public function getCooldownTag(): ?string
    {
        return "wind_charge";
    }
}
