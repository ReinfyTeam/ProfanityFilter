<?php

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\libs\_c6d3c962069a4ddc\SOFe\InfoAPI\Defaults;

use pocketmine\math\Vector3;
use pocketmine\world\Position;
use pocketmine\world\World;
use Shared\SOFe\InfoAPI\Display;
use Shared\SOFe\InfoAPI\KindMeta;
use Shared\SOFe\InfoAPI\Standard;
use ReinfyTeam\ProfanityFilter\libs\_c6d3c962069a4ddc\SOFe\InfoAPI\Indices;
use ReinfyTeam\ProfanityFilter\libs\_c6d3c962069a4ddc\SOFe\InfoAPI\ReflectUtil;
use function sprintf;

final class Positions {
	public static function register(Indices $indices) : void {
		$indices->registries->kindMetas->register(new KindMeta(Standard\PositionInfo::KIND, "Position", "A physical position in the game world", []));
		$indices->registries->displays->register(new Display(
			Standard\PositionInfo::KIND,
			fn($value) => $value instanceof Position ? sprintf("(%s, %s, %s) @ %s", $value->x, $value->y, $value->z, $value->world?->getDisplayName() ?? "null") : Display::INVALID,
		));

		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["x"], fn(Position $v) : float => $v->x,
			help: "X-coordinate of the position",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["y"], fn(Position $v) : float => $v->y,
			help: "Y-coordinate of the position",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["z"], fn(Position $v) : float => $v->z,
			help: "Z-coordinate of the position",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["world"], fn(Position $v) : ?World => $v->world,
			help: "World of the position",
		);

		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["add", "plus"],
			fn(Position $v, Vector3 $vector) : Position => Position::fromObject($v->addVector($vector), $v->world),
			help: "Move along the vector",
		);
		ReflectUtil::addClosureMapping(
			$indices, "infoapi:position", ["diff", "difference", "sub", "minus"],
			fn(Position $v, Position $from) : ?Vector3 => $v->world === $from->world ? $v->subtractVector($from) : null,
			help: "The vector from the `from` position to this position",
		);

		ReflectUtil::addClosureMapping($indices, "infoapi:position", ["dist", "distance"], fn(Position $v, Position $other) : float => $other->distance($v), help: "Distance between two positions");
	}
}