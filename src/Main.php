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

namespace XeonCh\Mace;

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use pocketmine\world\World;
use XeonCh\Mace\entity\WindCharge;
use pocketmine\inventory\CreativeInventory;

class Main extends PluginBase
{

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        EntityFactory::getInstance()->register(WindCharge::class, function (World $world, CompoundTag $nbt): WindCharge {
            return new WindCharge(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['wind_charge', 'minecraft:wind_charge_projectile']);
        self::registerItems();
        $this->getServer()->getAsyncPool()->addWorkerStartHook(function (int $worker): void {
            $this->getServer()->getAsyncPool()->submitTaskToWorker(
                new class extends AsyncTask
                {
                    public function onRun(): void
                    {
                        Main::registerItems();
                    }
                },
                $worker
            );
        });
    }
    public static function registerItems(): void
    {
        $mace = ExtraItems::MACE();
        $wind = ExtraItems::WIND();
        self::registerSimpleItem("minecraft:mace", $mace, ["mace_xeon", "mace_item"]);
        self::registerSimpleItem("minecraft:wind_charge", $wind, ["wind_xeon", "wind", "wind_charge_item"]);
    }

    /**
     * @param string[] $stringToItemParserNames
     */
    private static function registerSimpleItem(string $id, Item $item, array $stringToItemParserNames): void
    {
        GlobalItemDataHandlers::getDeserializer()->map($id, fn() => clone $item);
        GlobalItemDataHandlers::getSerializer()->map($item, fn() => new SavedItemData($id));

        if (!CreativeInventory::getInstance()->contains($item)) {           
           CreativeInventory::getInstance()->add($item);
        }

        foreach ($stringToItemParserNames as $name) {
            StringToItemParser::getInstance()->register($name, fn() => clone $item);
        }
    }
}
