<?php

namespace XeonCh\Mace\particle;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\ParticleIds;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\world\particle\Particle;

class WindParticle implements Particle
{
    public function encode(Vector3 $pos): array
    {
        return [LevelEventPacket::standardParticle(91, 0, $pos, ProtocolInfo::CURRENT_PROTOCOL)];
    }
}
