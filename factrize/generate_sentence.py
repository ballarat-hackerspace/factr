from random import choice

def generate_sentence(input_data):

    category = input_data['category']

    attribute = choice(input_data['attributes'])

    return "The {} near you has a {} value of {}. Makes you think, doesn't it?".format(category, attribute['name'], attribute['value'])