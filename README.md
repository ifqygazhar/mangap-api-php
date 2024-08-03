# Mangap RestApi

# Documentation

**BASE URL** = localhost:8000

## Setup Cache For Views If you want

Just create cache folder in root folder project.

## Recommended Comic

```markdown
/recommended
```

example : http://localhost:8000/recommended

**Response**
```markdown
{
  "status": "success",
  "message": "success",
  "data": [
    {
      "title": "Magic Emperor",
      "href": "/magic-emperor/",
      "rating": "8.00",
      "thumbnail": "https://komikcast.cz/wp-content/uploads/2019/10/magic-emperor.jpg?w=225&q=50",
      "chapter": "Ch.579",
      "type": "Manhua"
    },
    {
      "title": "Chronicles of the Demon Faction",
      "href": "/chronicles-of-the-demon-faction/",
      "rating": "8.00",
      "thumbnail": "https://komikcast.cz/wp-content/uploads/2023/03/chronicle.jpg?w=225&q=50",
      "chapter": "Ch.78",
      "type": "Manhwa"
    }
   ]
}  
```

## Popular Comic

```markdown
/popular
```

example : http://localhost:8000/popular

**Response**
```markdown
{
  "status": "success",
  "message": "success",
  "data": [
    {
      "title": "Eleceed",
      "href": "/eleceed/",
      "genre": "Action, Fantasy, Supernatural",
      "year": "2018",
      "thumbnail": "https://komikcast.cz/wp-content/uploads/2020/12/31013-e1609090459258.jpg?w=225&q=50"
    },
    {
      "title": "Rebirth Of The Urban Immortal Cultivator",
      "href": "/rebirth-of-the-urban-immortal-cultivator/",
      "genre": "Drama, Magic, School Life, Seinen, Supernatural",
      "year": "2018",
      "thumbnail": "https://komikcast.cz/wp-content/uploads/2022/07/rotu762352323-e1656913062334.jpg?w=225&q=50"
    }
  ]
}
```

## Detail Comic

```markdown
/detail/[endpoint]
```

example : http://localhost:8000/detail/solo-leveling

**Response**
```markdown
{
  "status": "success",
  "message": "success",
  "data": {
    "title": "Solo Leveling Bahasa Indonesia",
    "altTitle": "나 혼자만 레벨업",
    "updatedOn": "January 16, 2024",
    "rating": "8.99",
    "status": "Ongoing",
    "type": "Manhwa",
    "released": "2018",
    "author": "Chugong (Story), Jang, Sung-rak (Art)",
    "genre": [
      {
        "title": "Action",
        "href": "/action/"
      },
      {
        "title": "Adventure",
        "href": "/adventure/"
      },
      {
        "title": "Fantasy",
        "href": "/fantasy/"
      },
      {
        "title": "Shounen",
        "href": "/shounen/"
      }
    ],
    "description": "-",
    "thumbnail": "https://komikcast.cz/wp-content/uploads/2022/08/solev.jpg?w=400&q=70",
    "chapter": [
      {
        "title": "Chapter 179.2",
        "href": "/solo-leveling-chapter-179-2-bahasa-indonesia/",
        "date": "2 years ago"
      },
      {
        "title": "Chapter 179.1",
        "href": "/solo-leveling-chapter-179-1-bahasa-indonesia/",
        "date": "2 years ago"
      }
    ]
  }
}  
```

## Search Comic

```markdown
/search?keyword=[keyword]
```

example : http://localhost:8000/search?keyword=solo

**Response**
```markdown
{
  "status": "success",
  "message": "success",
  "data": [
    {
      "title": "Solo Max-Level Newbie",
      "type": "Manhwa",
      "chapter": "Ch.165",
      "rating": "7.37",
      "href": "/solo-max-level-newbie/",
      "thumbnail": "https://komikcast.cz/wp-content/uploads/2023/11/solomax.jpg?w=225&q=50"
    },
    {
      "title": "Solo Leveling: Ragnarok",
      "type": "Manhwa",
      "chapter": "Ch.13",
      "rating": "8.88",
      "href": "/solo-leveling-ragnarok/",
      "thumbnail": "https://komikcast.cz/wp-content/uploads/2024/07/slr532453243223-e1722440897888.jpg?w=225&q=50"
    }
  ]
}
```

## Read Comic

```markdown
/read/[endpoint]
```

example : http://localhost:8000/read/solo-leveling-chapter-33-bahasa-indonesia/

**Response**
```markdown
{
  "status": "success",
  "message": "success",
  "data": [
    {
      "title": "Solo Leveling Chapter 33 Bahasa Indonesia",
      "prev": "/solo-leveling-chapter-32-bahasa-indonesia/",
      "next": "/solo-leveling-chapter-34-bahasa-indonesia/",
      "panel": [
        "https://sv1.imgkc1.my.id/wp-content/img/S/Solo_Leveling/033/001.jpg",
        "https://sv1.imgkc1.my.id/wp-content/img/S/Solo_Leveling/033/002.jpg",
        "https://sv1.imgkc1.my.id/wp-content/img/S/Solo_Leveling/033/003.jpg",
        "https://sv1.imgkc1.my.id/wp-content/img/S/Solo_Leveling/033/004.jpg"
      ]
    }
  ]
}
```

## Genre

```markdown
/genre
```

example : http://localhost:8000/genre

**Response**
```markdown
{
  "status": "success",
  "message": "success",
  "data": [
    {
      "title": "4-Koma (38)",
      "href": "/4-koma/"
    },
    {
      "title": "Action (4052)",
      "href": "/action/"
    },
  ]
}
```

## Genre Comic

```markdown
/genre/[endpoint]
```

example : http://localhost:8000/genres/action/1

**Response**
```markdown
{
  "status": "success",
  "message": "success",
  "data": {
    "current_page": 1,
    "length_page": 68,
    "data": [
      {
        "title": "Absolute Necromancer",
        "chapter": "Ch.62",
        "type": "Manhwa",
        "href": "/absolute-necromancer/",
        "rating": "7.00",
        "thumbnail": "https://komikcast.cz/wp-content/uploads/2024/03/absolute.jpeg?w=225&q=50"
      },
      {
        "title": "Magic Emperor",
        "chapter": "Ch.579",
        "type": "Manhua",
        "href": "/magic-emperor/",
        "rating": "8.00",
        "thumbnail": "https://komikcast.cz/wp-content/uploads/2019/10/magic-emperor.jpg?w=225&q=50"
      }
    ]
  }
} 
```
