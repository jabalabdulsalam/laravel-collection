<?php

namespace Tests\Feature;

use App\Data\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\LazyCollection;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class CollectionTest extends TestCase
{
    public function testCreateCollection()
    {
        $collection = collect([1, 2, 3]);
        $this->assertEqualsCanonicalizing([1, 2, 3], $collection->all());
    }

    public function testForEach()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        foreach ($collection as $key => $value) {
            self::assertEquals($key + 1, $value);
        }
    }

    public function testCrud()
    {
        $collection = collect([]);
        $collection->push(1, 2, 3);
        $this->assertEqualsCanonicalizing([1, 2, 3], $collection->all());

        $result = $collection->pop();
        $this->assertEquals(3, $result);
        $this->assertEqualsCanonicalizing([1, 2], $collection->all());
    }

    public function testMap()
    {
        $collection = collect([1, 2, 3]);
        $result = $collection->map(function ($item) {
            return $item * 2;
        });

        $this->assertEqualsCanonicalizing([2, 4, 6], $result->all());
    }

    public function testMapInto()
    {
        $collection = collect(["Jabal"]);
        $result = $collection->mapInto(Person::class);
        $this->assertEquals([new Person("Jabal")], $result->all());
    }

    public function testMapSpread()
    {
        $collection = collect([
            ["Jabal", "Salam"],
            ["Darmisah", "Hanum"]
        ]);

        $result = $collection->mapSpread(function ($firstName, $lastName){
            $fullName = $firstName . " " . $lastName;
            return new Person($fullName);
        });

        $this->assertEquals([
            new Person("Jabal Salam"),
            new Person("Darmisah Hanum")
        ], $result->all());
    }

    public function testMapToGroup()
    {
        $collection = collect([
            [
                "name" => "Jabal",
                "departement" => "IT"
            ],
            [
                "name" => "Abdul",
                "departement" => "IT"
            ],
            [
                "name" => "Hanum",
                "departement" => "HR"
            ]
        ]);

        $result = $collection->mapToGroups(function ($person){
            return [
                $person["departement"] => $person["name"]];
        });

        $this->assertEquals([
            "IT" => collect(["Jabal", "Abdul"]),
            "HR" => collect(["Hanum"])
        ], $result->all());
    }

    public function testZip()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->zip($collection2);

        $this->assertEquals([
            collect([1, 4]),
            collect([2, 5]),
            collect([3, 6]),
        ], $collection3->all());
    }

    public function testConcat()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->concat($collection2);

        $this->assertEqualsCanonicalizing([1,2,3,4,5,6], $collection3->all());
    }

    public function testCombine()
    {
        $collection1 = collect(["name", "country"]);
        $collection2 = collect(["Jabal", "Indonesia"]);
        $collection3 = $collection1->combine($collection2);

        $this->assertEqualsCanonicalizing([
            "name" => "Jabal",
            "country" => "Indonesia"
        ], $collection3->all());
    }

    public function testCollapse()
    {
        $collection = collect([
            [1,2,3],
            [4,5,6],
            [7,8,9],
        ]);
        $result = $collection->collapse();
        $this->assertEqualsCanonicalizing([1,2,3,4,5,6,7,8,9], $result->all());
    }

    public function testFlatMap()
    {
        $collection = collect([
            [
                "name" => "Jabal",
                "hobbies" => ["Coding", "Gaming"]
            ],
            [
                "name" => "Abdul",
                "hobbies" => ["Reading", "Football"]
            ]
        ]);
        $result = $collection->flatMap(function ($item){
            $hobbies = $item["hobbies"];
            return $hobbies;
        });

        $this->assertEqualsCanonicalizing(["Coding", "Gaming", "Reading", "Football"], $result->all());
    }

    public function testStringRepresentation()
    {
        $collection = collect(["Jabal", "Abdul", "Salam"]);

        $this->assertEquals("Jabal-Abdul-Salam", $collection->join("-"));
        $this->assertEquals("Jabal-Abdul_Salam", $collection->join("-", "_"));
    }

    public function testFilter()
    {
        $collection = collect([
            "Jabal" => 100,
            "Abdul" => 80,
            "Salam" => 90
        ]);

        $result = $collection->filter(function ($value, $key){
            return $value >= 90;
        });

        $this->assertEquals([
            "Jabal" => 100,
            "Salam" => 90
        ], $result->all());
    }

    public function testFilterIndex()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);
        $result = $collection->filter(function ($value, $key){
            return $value % 2 == 0;
        });

        $this->assertEqualsCanonicalizing([2,4,6,8,10], $result->all());
    }

    public function testPartition()
    {
        $collection = collect([
            "Jabal" => 100,
            "Abdul" => 80,
            "Salam" => 90
        ]);

        [$result1, $result2] = $collection->partition(function ($value, $key){
            return $value >= 90;
        });

        $this->assertEquals([
            "Jabal" => 100,
            "Salam" => 90
        ], $result1->all());

        $this->assertEquals([
            "Abdul" => 80
        ], $result2->all());
    }

    public function testTesting()
    {
        $collection = collect(["Jabal", "Abdul", "Salam"]);
        $this->assertTrue($collection->contains("Jabal"));
        $this->assertTrue($collection->contains(function ($value, $key){
            return $value == "Abdul";
        }));
    }

    public function testGrouping()
    {
        $collection = collect([
            [
                "name" => "Jabal",
                "departement" => "IT"
            ],
            [
                "name" => "Abdul",
                "departement" => "IT"
            ],
            [
                "name" => "Salam",
                "departement" => "HR"
            ]
        ]);

        $result = $collection->groupBy("departement");

        assertEquals([
            "IT" => collect([
                [
                    "name" => "Jabal",
                    "departement" => "IT"
                ],
                [
                    "name" => "Abdul",
                    "departement" => "IT"
                ]
            ]),
            "HR" => collect([
                [
                    "name" => "Salam",
                    "departement" => "HR"
                ]
            ])
        ], $result->all());

        $result = $collection->groupBy(function ($value, $key){
            return strtolower($value["departement"]) ;
        });

        assertEquals([
            "it" => collect([
                [
                    "name" => "Jabal",
                    "departement" => "IT"
                ],
                [
                    "name" => "Abdul",
                    "departement" => "IT"
                ]
            ]),
            "hr" => collect([
                [
                    "name" => "Salam",
                    "departement" => "HR"
                ]
            ])
        ], $result->all());
    }

    public function testSlicing()
    {
        $collection  = collect([1,2,3,4,5,6,7,8,9]);
        $result = $collection->slice(3);

        $this->assertEqualsCanonicalizing([4,5,6,7,8,9], $result->all());

        $result = $collection->slice(3, 2);
        $this->assertEqualsCanonicalizing([4,5], $result->all());
    }

    public function testTake()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9]);

        $result = $collection->take(3);
        $this->assertEqualsCanonicalizing([1,2,3], $result->all());

        $result = $collection->takeUntil(function ($value, $key){
            return $value == 3;
        });

        $this->assertEqualsCanonicalizing([1,2], $result->all());
    }

    public function testSkip()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9]);

        $result = $collection->skip(3);
        $this->assertEqualsCanonicalizing([4,5,6,7,8,9], $result->all());

        $result = $collection->skipUntil(function ($value, $key){
            return $value == 3;
        });
        $this->assertEqualsCanonicalizing([3,4,5,6,7,8,9], $result->all());

        $result = $collection->skipWhile(function ($value, $key){
            return $value < 3;
        });
        $this->assertEqualsCanonicalizing([3,4,5,6,7,8,9], $result->all());
    }

    public function testChunk()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9,10]);

        $result = $collection->chunk(3);

        $this->assertEqualsCanonicalizing([1,2,3], $result->all()[0]->all());
        $this->assertEqualsCanonicalizing([4,5,6], $result->all()[1]->all());
        $this->assertEqualsCanonicalizing([7,8,9], $result->all()[2]->all());
        $this->assertEqualsCanonicalizing([10], $result->all()[3]->all());
    }

    public function testFirst()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9]);
        $result = $collection->first();
        $this->assertEquals(1, $result);

        $result = $collection->first(function ($value, $key){
            return $value > 5;
        });

        $this->assertEquals(6, $result);
    }

    public function testLast()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9]);
        $result = $collection->last();
        $this->assertEquals(9, $result);

        $result = $collection->last(function ($value, $key){
            return $value < 5;
        });
        $this->assertEquals(4, $result);

    }

    public function testRandom()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9]);
        $result = $collection->random();

        $this->assertTrue(in_array($result, [1,2,3,4,5,6,7,8,9]));

//        $result = $collection->random(5);
//        $this->assertEqualsCanonicalizing([1,2,3,4,5], $result->all());
    }

    public function testCheckingExistence()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9]);
        $this->assertTrue($collection->isNotEmpty());
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->contains(1));
        $this->assertFalse($collection->contains(10));
        $this->assertTrue($collection->contains(function ($value, $key){
            return $value == 8;
        }));
    }

    public function testOrdering()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9]);
        $result = $collection->sort();
        $this->assertEqualsCanonicalizing([1,2,3,4,5,6,7,8,9], $result->all());

        $result = $collection->sortDesc();
        $this->assertEqualsCanonicalizing([9,8,7,6,5,4,3,2,1], $result->all());
    }

    public function testAggregate()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9]);
        $result = $collection->sum();
        $this->assertEquals(45, $result);

        $result = $collection->avg();
        $this->assertEquals(5, $result);

        $result = $collection->min();
        $this->assertEquals(1, $result);

        $result = $collection->max();
        $this->assertEquals(9, $result);
    }

    public function testReduce()
    {
        $collection = collect([1,2,3,4,5,6,7,8,9]);
        $result = $collection->reduce(function ($carry, $item){
            return $carry + $item;
        });
        $this->assertEquals(45, $result);
    }

    public function testLazyCollection()
    {
        $collection = LazyCollection::make(function (){
            $value = 0;
            while (true){
                yield $value;
                $value++;
            }
        });

        $result = $collection->take(10);
        $this->assertEqualsCanonicalizing([0,1,2,3,4,5,6,7,8,9], $result->all());

    }


}
