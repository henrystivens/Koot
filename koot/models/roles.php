<?php
class Roles 
{
    // Roles in the Open-Source under BSD3 License
    private const ROLES = [
        self::SUPER_ADMIN => 'Super Admin',
        self::EDITOR => 'Editor',
        self::AUTHOR => 'Author',
        self::GUEST => 'Guest',
    ];

    /**
     * @var int Role ID for Super Admin.
     */
    private const SUPER_ADMIN = 1;
    
    /**
     * @var int Role ID for Editor.
     */
    private const EDITOR = 2;

    /**
     * @var int Role ID for Author.
     */
    private const AUTHOR = 3;

    /**
     * @var int Role ID for Guest.
     */
    private const GUEST = 0;

    /**
     * @var int Role ID.
     */
    public $id;

    /**
     * @var string Role name.
     */
    public $name;

    /**
     * Constructor to initialize the role.
     * @param array $data The data to initialize the role.
     */
    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
    }

    /**
     * Get all roles.
     * @return array 
     */
    public static function all(): array
    {
        $roles = [];

        foreach (self::ROLES as $id => $name) {
            $roles[] = new Roles([
                'id' => $id,
                'name' => $name
            ]);
        }

        return $roles;
    }
}
