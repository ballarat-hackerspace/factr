import random
import dateutil.parser as dup
from datetime import datetime

awesome_sentence_generation_not_working = True  # Deadlines!

reply_forms_notime = []

reply_forms_notime.append("The {category} near you has a {attribute} value of {value}.")
reply_forms_notime.append("{value} {attribute}!? That's right, take a look at that {category} near you.")
reply_forms_notime.append("Nearby, you'll see a {category} with {value} {attribute}.")
reply_forms_notime.append("Did you know that the nearest {category} has an {attribute} value of {value}.")
reply_forms_notime.append("Did you know that the nearest {category} has an {value} {attribute}s?")


reply_forms_time = []
reply_forms_time.append("Since {datetime_value}, the {category} near you has a {attribute} value of {value}.")
reply_forms_time.append("Did you know that since {datetime_value}, the {category} near you has a {attribute} value of {value}.")
reply_forms_time.append("On {datetime_value}, the {category} near you has a {attribute} value of {value}.")
reply_forms_time.append("Did you know that on {datetime_value}, the {category} near you has a {attribute} value of {value}.")



def generate_sentence(input_data):

    category = input_data['category']
    datetime_value = input_data.get('datetime', None)
    attribute = random.choice(input_data['attributes'])

    # IBM Watson (or similar) goes here, but the training couldn't be done to a reliable level in time.
    reply_options = None
    if awesome_sentence_generation_not_working:
        # Choose either a temporal or non-temporal type
        if "datetime" in input_data and input_data['datetime']:
            reply_options = reply_forms_time
            # Format datetime to be human readable
            dt = None
            if isinstance(datetime_value, int):
                dt = datetime.fromtimestamp(datetime_value)
            elif not isinstance(datetime_value, datetime):
                dt = dup.parse(datetime_value)
            datetime_value = dt.strftime("%A %d %M, %Y")
        else:
            reply_options = reply_forms_notime

    # Choose a reply form at random and populate it
    return random.choice(reply_options).format(category=category, attribute=attribute['name'],
                                               value=attribute['value'], datetime_value=datetime_value)