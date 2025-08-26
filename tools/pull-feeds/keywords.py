class Keywords:
    keywords = ["japan","indonesia","malaysia","brunei","cambodia","laos","myanmar","philippines","singapore","thailand","vietnam","asean","southeast asia","south east asia"]

    @staticmethod
    def to_country(keyword):
        switcher = {
            "japan":"Japan",
            "indonesia":"Indonesia",
            "malaysia":"Malaysia",
            "brunei":"Brunei",
            "cambodia":"Cambodia",
            "laos":"Laos",
            "myanmar":"Myanmar",
            "philippines":"Philippines",
            "singapore":"Singapore",
            "thailand":"Thailand",
            "vietnam":"Vietnam",
            "asean":"035 - South-eastern Asia",
            "southeast asia":"035 - South-eastern Asia",
            "south east asia":"035 - South-eastern Asia",
        }

        return switcher.get(keyword)

